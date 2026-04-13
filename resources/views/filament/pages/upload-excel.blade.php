<x-filament::page>
    <div class="space-y-6 m-6">
        @if (session('success'))
            <div class="p-4 border border-green-300 bg-green-50 text-green-800 rounded-lg flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 border border-red-300 bg-red-50 text-red-800 rounded-lg flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd" />
                </svg>
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 border-l-4 border-red-500 bg-red-50 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 flex-shrink-0 text-red-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <div>
                        <h3 class="font-bold text-red-900 mb-2">⚠️ Fout bij uploaden</h3>
                        <ul class="space-y-2 text-red-800 text-sm">
                            @foreach ($errors->all() as $error)
                                <li class="flex items-start gap-2">
                                    <span class="text-red-600 font-bold mt-1">•</span>
                                    <span>{{ $error }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-lg border border-gray-200">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-8 rounded-t-lg">
                <h1 class="text-3xl font-bold text-white mb-2">Excel Bestand Uploaden</h1>
                <p class="text-blue-100">Importeer uw gegevens uit een Excel bestand</p>
            </div>

            <!-- Content -->
            <div class="px-8 py-8">
                <form action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data"
                    class="space-y-8">
                    @csrf

                    <!-- Instructions -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-semibold text-blue-900 mb-3">Instructies:</h3>
                        <ul class="space-y-2 text-sm text-blue-800">
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 font-bold mt-0.5">1.</span>
                                <span>Selecteer een Excel bestand (.xlsx of .xls) van uw computer</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 font-bold mt-0.5">2.</span>
                                <span>Het bestand mag maximaal 5MB groot zijn</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 font-bold mt-0.5">3.</span>
                                <span>Klik op "Upload" om het bestand in te dienen</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 font-bold mt-0.5">4.</span>
                                <span>De gegevens worden verwerkt en opgeslagen</span>
                            </li>
                        </ul>
                    </div>

                    <!-- File Upload -->
                    <div class="space-y-3">
                        <label for="excel_file" class="block text-sm font-semibold text-gray-700">
                            Selecteer Excel bestand
                        </label>
                        <div class="relative">
                            <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls" required
                                class="block w-full text-sm text-gray-500
                                  file:mr-4 file:py-3 file:px-6
                                  file:rounded-lg file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-blue-600 file:text-white
                                  hover:file:bg-blue-700
                                  file:cursor-pointer
                                  cursor-pointer
                                  border border-gray-300 rounded-lg
                                  p-3">
                        </div>
                        <p class="text-xs text-gray-600 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            Ondersteunde formaten: .xlsx, .xls | Maximale grootte: 5MB
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-600">Alle velden zijn verplicht</p>
                        <button type="submit"
                            class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition duration-200 flex items-center gap-2 shadow-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            Upload Bestand
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Info Box -->
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
            <h3 class="font-semibold text-amber-900 mb-2 flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd" />
                </svg>
                Tip
            </h3>
            <p class="text-sm text-amber-800">Zorg ervoor dat uw Excel bestand goed geformatteerd is met vaste koppen in
                de eerste rij voor optimale verwerking.</p>
        </div>
    </div>
</x-filament::page>
