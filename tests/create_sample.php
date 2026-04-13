<?php

use Maatwebsite\Excel\Excel;

// Test Excel file aanmaken
$path = 'tests/sample.xlsx';

// Create sample data
$data = [
    ['Voornaam', 'Achternaam', 'Email', 'Telefoonnummer'],
    ['Jan', 'Jansen', 'jan@example.com', '0612345678'],
    ['Marie', 'Pieterse', 'marie@example.com', '0687654321'],
    ['Peter', 'Hendriks', 'peter@example.com', '0698765432'],
];

// Write to CSV first (tested method)
$csv = fopen('storage/uploads/sample.csv', 'w');
foreach ($data as $row) {
    fputcsv($csv, $row);
}
fclose($csv);

echo "Sample bestand aangemaakt!";
