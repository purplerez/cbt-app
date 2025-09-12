interface LoadingSpinnerProps {
     message?: string;
}

export function LoadingSpinner({ message = "Memuat..." }: LoadingSpinnerProps) {
     return (
          <div className="flex flex-col items-center justify-center min-h-screen bg-white">
               <div className="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mb-4"></div>
               <p className="text-gray-600 text-lg">{message}</p>
          </div>
     );
}
