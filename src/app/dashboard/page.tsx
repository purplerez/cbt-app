export default function DashboardPage() {
     return (
          <div className="flex flex-col gap-10 p-4 sm:p-8 md:p-10 lg:p-16 min-h-screen bg-white">

               {/* Navbar */}
               <div className="flex flex-col sm:flex-row items-center justify-between gap-4 sm:gap-0">
                    {/* Left */}
                    <div className="flex items-center gap-2">
                         <div className="rounded-full w-10 h-10 sm:w-12 sm:h-12 bg-gray-400"></div>
                         <div className="h-5 w-24 sm:h-6 sm:w-40 bg-gray-400"></div>
                    </div>
                    {/* Right */}
                    <div>
                         <div className="w-8 h-8 sm:w-10 sm:h-10 bg-gray-400"></div>
                    </div>
               </div>

               {/* Content exam */}
               <div className="flex flex-col gap-6 sm:gap-10 px-2 sm:px-5">
                    {/* Soal */}
                    <div>
                         <p className="text-base sm:text-lg md:text-xl text-gray-700">
                              Lorem ipsum dolor sit amet consectetur adipisicing elit. Reprehenderit officiis quia architecto tempore temporibus, adipisci nobis natus at a alias corrupti repudiandae. Odio, asperiores! Reprehenderit?
                         </p>
                    </div>
                    {/* Pilihan Jawaban */}
                    <div className="flex flex-col gap-2">
                         {Array.from({ length: 4 }).map((_, index) => (
                              <div key={index} className="flex items-center gap-2 p-2 rounded-md bg-gray-50 hover:bg-gray-100 transition-all">
                                   <div className="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-gray-400"></div>
                                   <p className="text-sm sm:text-base">Jawaban ujian no {index + 1}</p>
                              </div>
                         ))}
                    </div>
               </div>

               {/* Button Action */}
               <div className="flex flex-col sm:flex-row items-center justify-between gap-4 mt-8">
                    <button className="w-full sm:w-auto px-4 py-2 bg-blue-500 text-white rounded shadow hover:bg-blue-600 transition-all">Kembali</button>
                    <button className="w-full sm:w-auto px-4 py-2 bg-green-500 text-white rounded shadow hover:bg-green-600 transition-all">Selanjutnya</button>
               </div>
          </div>
     );
}