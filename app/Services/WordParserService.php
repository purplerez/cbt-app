<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\AbstractContainer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class WordParserService
{
    public function parseWordDocument($filePath)
    {
        // Try simple text extraction first
        $allText = $this->extractAllTextSimple($filePath);
        
        $this->log('Extracted text from Word: ' . substr($allText, 0, 500));
        
        if (empty($allText)) {
            $this->log('No text extracted from Word document', 'error');
            return [];
        }
        
        $questions = [];
        $currentQuestion = null;
        
        // Split by lines
        $lines = explode("\n", $allText);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) {
                continue;
            }
            
            $this->log('Processing line: ' . $line);
            
            // Check if this is a new question (starts with number)
            if (preg_match('/^(\d+)\.?\s+(.+)/s', $line, $matches)) {
                // Save previous question if exists
                if ($currentQuestion !== null) {
                    $questions[] = $currentQuestion;
                }
                
                $this->log('Found question #' . $matches[1] . ': ' . substr($matches[2], 0, 50));
                
                $currentQuestion = [
                    'question_text' => trim($matches[2]),
                    'option_a' => null,
                    'option_a_image' => null,
                    'option_b' => null,
                    'option_b_image' => null,
                    'option_c' => null,
                    'option_c_image' => null,
                    'option_d' => null,
                    'option_d_image' => null,
                    'correct_answer' => null,
                    'points' => 1,
                    'image_path' => null,
                ];
            }
            // Check if this is an option (A., B., C., D.)
            elseif (preg_match('/^([A-D])\.?\s+(.+)/i', $line, $matches)) {
                if ($currentQuestion !== null) {
                    $option = strtolower($matches[1]);
                    $currentQuestion['option_' . $option] = trim($matches[2]);
                    $this->log('Added option ' . strtoupper($option) . ': ' . substr($matches[2], 0, 30));
                }
            }
            // Check if this is the answer key
            elseif (preg_match('/(jawaban|answer|kunci):\s*([A-D])/i', $line, $matches)) {
                if ($currentQuestion !== null) {
                    $currentQuestion['correct_answer'] = strtoupper($matches[2]);
                    $this->log('Set answer: ' . $matches[2]);
                }
            }
            // Check if this is points/score
            elseif (preg_match('/(poin|nilai|score|bobot|skor|point):\s*(\d+)/i', $line, $matches)) {
                if ($currentQuestion !== null) {
                    $currentQuestion['points'] = intval($matches[2]);
                    $this->log('Set points: ' . $matches[2]);
                }
            }
            // Otherwise append to current question text if no options yet
            elseif ($currentQuestion !== null && empty($currentQuestion['option_a'])) {
                $currentQuestion['question_text'] .= ' ' . $line;
            }
        }
        
        // Add the last question
        if ($currentQuestion !== null) {
            $questions[] = $currentQuestion;
        }
        
        // Now extract and assign images
        try {
            $this->extractAndAssignImages($filePath, $questions);
        } catch (\Exception $e) {
            $this->log('Error extracting images: ' . $e->getMessage(), 'error');
        }
        
        $this->log('Total questions extracted: ' . count($questions));
        
        return $questions;
    }
    
    /**
     * Extract document structure with text and images in order
     */
    private function extractDocumentStructure($filePath)
    {
        $structure = [];
        
        try {
            $zip = new ZipArchive();
            
            if ($zip->open($filePath) !== true) {
                return $structure;
            }
            
            // Get document.xml content
            $documentXml = $zip->getFromName('word/document.xml');
            
            if (!$documentXml) {
                $zip->close();
                return $structure;
            }
            
            // Parse XML
            $dom = new \DOMDocument();
            $dom->loadXML($documentXml);
            $xpath = new \DOMXPath($dom);
            
            // Register namespaces
            $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
            $xpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
            $xpath->registerNamespace('pic', 'http://schemas.openxmlformats.org/drawingml/2006/picture');
            $xpath->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            
            // Get document relationships to map images
            $relsXml = $zip->getFromName('word/_rels/document.xml.rels');
            $imageMap = [];
            
            if ($relsXml) {
                $relsDom = new \DOMDocument();
                $relsDom->loadXML($relsXml);
                $relsXpath = new \DOMXPath($relsDom);
                $relationships = $relsXpath->query('//Relationship[@Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image"]');
                
                foreach ($relationships as $rel) {
                    $id = $rel->getAttribute('Id');
                    $target = $rel->getAttribute('Target');
                    $imageMap[$id] = 'word/' . $target;
                }
            }
            
            // Get all paragraphs in order
            $paragraphs = $xpath->query('//w:p');
            
            foreach ($paragraphs as $paragraph) {
                // Check for text
                $textNodes = $xpath->query('.//w:t', $paragraph);
                $text = '';
                foreach ($textNodes as $textNode) {
                    $text .= $textNode->textContent;
                }
                
                if (!empty(trim($text))) {
                    $structure[] = [
                        'type' => 'text',
                        'content' => $text,
                    ];
                }
                
                // Check for images in this paragraph
                $drawings = $xpath->query('.//w:drawing', $paragraph);
                
                foreach ($drawings as $drawing) {
                    $blips = $xpath->query('.//a:blip', $drawing);
                    
                    foreach ($blips as $blip) {
                        $embed = $blip->getAttribute('r:embed');
                        
                        if (isset($imageMap[$embed])) {
                            $imagePath = str_replace('\\', '/', $imageMap[$embed]);
                            $imageContent = $zip->getFromName($imagePath);
                            
                            if ($imageContent) {
                                // Get extension from path
                                preg_match('/\.(png|jpg|jpeg|gif|bmp)$/i', $imagePath, $matches);
                                $extension = $matches[1] ?? 'png';
                                
                                $structure[] = [
                                    'type' => 'image',
                                    'content' => $imageContent,
                                    'extension' => $extension,
                                ];
                                
                                $this->log("Found image: " . $imagePath);
                            }
                        }
                    }
                }
            }
            
            $zip->close();
        } catch (\Exception $e) {
            $this->log('Error extracting document structure: ' . $e->getMessage(), 'error');
        }
        
        return $structure;
    }
    
    /**
     * Save image to storage
     */
    private function saveImage($imageContent, $extension)
    {
        $fileName = 'question_' . time() . '_' . Str::random(10) . '.' . $extension;
        $destinationPath = 'questions/images/' . $fileName;
        
        Storage::disk('public')->put($destinationPath, $imageContent);
        
        $this->log("Saved image: " . $destinationPath);
        
        return $destinationPath;
    }
    
    /**
     * Extract images from DOCX and intelligently assign to questions/options
     */
    private function extractAndAssignImages($filePath, &$questions)
    {
        if (count($questions) === 0) {
            return;
        }
        
        $zip = new ZipArchive();
        
        if ($zip->open($filePath) !== true) {
            $this->log('Cannot open ZIP file for image extraction', 'error');
            return;
        }
        
        try {
            // Read document.xml and document.xml.rels to map images
            $documentXml = $zip->getFromName('word/document.xml');
            $relsXml = $zip->getFromName('word/_rels/document.xml.rels');
            
            if (!$documentXml || !$relsXml) {
                $this->log('Cannot read document XML or relationships', 'error');
                $zip->close();
                return;
            }
            
            // Parse relationships to build image ID map
            $imageMap = [];
            $relsDoc = new \DOMDocument();
            $relsDoc->loadXML($relsXml);
            
            $relationships = $relsDoc->getElementsByTagName('Relationship');
            foreach ($relationships as $rel) {
                $type = $rel->getAttribute('Type');
                if (strpos($type, '/image') !== false) {
                    $id = $rel->getAttribute('Id');
                    $target = $rel->getAttribute('Target');
                    $imageMap[$id] = 'word/' . $target;
                }
            }
            
            $this->log('Found ' . count($imageMap) . ' image relationships');
            
            if (count($imageMap) === 0) {
                $zip->close();
                return;
            }
            
            // Parse document.xml to find images
            $doc = new \DOMDocument();
            $doc->loadXML($documentXml);
            $xpath = new \DOMXPath($doc);
            $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
            $xpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
            $xpath->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            
            // Extract all text and image references in order
            $elements = [];
            $paragraphs = $xpath->query('//w:p');
            
            foreach ($paragraphs as $paragraph) {
                // Get text
                $textNodes = $xpath->query('.//w:t', $paragraph);
                $text = '';
                foreach ($textNodes as $textNode) {
                    $text .= $textNode->textContent;
                }
                
                if (!empty(trim($text))) {
                    $elements[] = ['type' => 'text', 'content' => trim($text)];
                }
                
                // Get images in this paragraph
                $blips = $xpath->query('.//a:blip', $paragraph);
                foreach ($blips as $blip) {
                    $embedId = $blip->getAttribute('r:embed');
                    if (isset($imageMap[$embedId])) {
                        $imagePath = $imageMap[$embedId];
                        $imageContent = $zip->getFromName($imagePath);
                        
                        if ($imageContent) {
                            preg_match('/\.(png|jpg|jpeg|gif|bmp)$/i', $imagePath, $matches);
                            $extension = $matches[1] ?? 'png';
                            
                            $elements[] = [
                                'type' => 'image',
                                'content' => $imageContent,
                                'extension' => $extension,
                            ];
                        }
                    }
                }
            }
            
            $this->log('Extracted ' . count($elements) . ' elements (text + images)');
            
            // Now assign images to questions based on proximity
            $currentQuestionIndex = -1;
            $lastOption = null;
            
            foreach ($elements as $element) {
                if ($element['type'] === 'text') {
                    $text = $element['content'];
                    
                    // Check if this is a new question
                    if (preg_match('/^(\d+)\.?\s+/', $text)) {
                        $currentQuestionIndex++;
                        $lastOption = null;
                    }
                    // Check if this is an option
                    elseif (preg_match('/^([A-D])\.?\s+/i', $text, $matches)) {
                        $lastOption = strtolower($matches[1]);
                    }
                }
                elseif ($element['type'] === 'image' && $currentQuestionIndex >= 0 && $currentQuestionIndex < count($questions)) {
                    // Save image
                    $imagePath = $this->saveImage($element['content'], $element['extension']);
                    
                    // Assign to the appropriate location
                    if ($lastOption !== null && isset($questions[$currentQuestionIndex]['option_' . $lastOption])) {
                        $questions[$currentQuestionIndex]['option_' . $lastOption . '_image'] = $imagePath;
                        $this->log("Assigned image to Q" . ($currentQuestionIndex + 1) . " option " . strtoupper($lastOption));
                    }
                    elseif ($lastOption === null) {
                        $questions[$currentQuestionIndex]['image_path'] = $imagePath;
                        $this->log("Assigned image to Q" . ($currentQuestionIndex + 1) . " question");
                    }
                }
            }
            
            $zip->close();
            
        } catch (\Exception $e) {
            $this->log('Error in extractAndAssignImages: ' . $e->getMessage(), 'error');
            $zip->close();
        }
    }
    
    /**
     * Log helper function
     */
    private function log($message, $level = 'info')
    {
        if (function_exists('\\Log::' . $level)) {
            Log::{$level}($message);
        }
    }
    
    private function extractAllTextSimple($filePath)
    {
        $text = '';
        
        try {
            $zip = new ZipArchive();
            
            if ($zip->open($filePath) === true) {
                // Read document.xml which contains the main text
                $content = $zip->getFromName('word/document.xml');
                
                if ($content) {
                    // Simple approach: use regex to extract text between <w:t> tags
                    preg_match_all('/<w:t[^>]*>(.*?)<\/w:t>/s', $content, $matches);
                    
                    if (!empty($matches[1])) {
                        $text = implode("\n", $matches[1]);
                    } else {
                        // Fallback: strip all XML tags
                        $text = strip_tags($content);
                        // Add newlines for paragraph breaks
                        $text = preg_replace('/<w:p[^>]*>/i', "\n", $text);
                    }
                    
                    // Decode XML entities
                    $text = html_entity_decode($text);
                }
                
                $zip->close();
            }
        } catch (\Exception $e) {
            $this->log('Error in extractAllTextSimple: ' . $e->getMessage(), 'error');
        }
        
        return $text;
    }
}
