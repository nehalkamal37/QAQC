<?php

namespace App\Http\Controllers;

use Smalot\PdfParser\Parser;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function read()
    {
        $path = public_path('Plumbing QC Checklist.pdf');
        
        // Check if file exists
        if (!file_exists($path)) {
            return response()->json([
                'error' => 'PDF file not found: ' . $path
            ], 404);
        }
        
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($path);
            
            // Extract all text
            $text = $pdf->getText();
            
            // Get PDF metadata
            $details = $pdf->getDetails();
            
            // Extract structured data from the text
            $formData = $this->extractFormData($text);
            
            return response()->json([
                'success' => true,
                'file_info' => [
                    'file_name' => 'Plumbing QC Checklist.pdf',
                    'file_size' => filesize($path),
                    'total_pages' => $details['Pages'] ?? 'Unknown'
                ],
                'metadata' => $details,
                'form_data' => $formData,
                'text_content' => $text // Full text content
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to parse PDF: ' . $e->getMessage(),
                'file_path' => $path
            ], 500);
        }
    }
    
    private function extractFormData($text)
    {
        $formData = [
            'checkboxes' => [],
            'text_fields' => [],
            'sections' => []
        ];
        
        // Split text into lines for analysis
        $lines = explode("\n", $text);
        
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            
            if (empty($trimmedLine)) {
                continue;
            }
            
            // Detect checkboxes (common patterns)
            if (preg_match('/(□|☐|☑|✓|_+\s*)\s*(.+)/', $trimmedLine, $matches)) {
                $formData['checkboxes'][] = [
                    'item' => trim($matches[2]),
                    'checked' => in_array($matches[1], ['☑', '✓']),
                    'type' => 'checkbox'
                ];
            }
            // Detect text fields (lines with labels followed by underscores)
            elseif (preg_match('/([A-Za-z\s]+):\s*_+/', $trimmedLine, $matches)) {
                $formData['text_fields'][] = [
                    'label' => trim($matches[1]),
                    'type' => 'text_field'
                ];
            }
            // Detect section headers (all caps or numbered sections)
            elseif (preg_match('/^([A-Z][A-Z\s]+)$|^(\d+\.\s+[A-Z])/', $trimmedLine)) {
                $formData['sections'][] = [
                    'title' => $trimmedLine,
                    'type' => 'section_header'
                ];
            }
        }
        
        return $formData;
    }
    
    /**
     * Extract only the form structure without full text
     */
    public function readFormStructure()
    {
        $path = public_path('Plumbing QC Checklist.pdf');
        
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($path);
            $text = $pdf->getText();
            
            $formStructure = $this->extractFormStructure($text);
            
            return response()->json([
                'success' => true,
                'form_structure' => $formStructure
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to extract form structure: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function extractFormStructure($text)
    {
        $structure = [];
        $lines = explode("\n", $text);
        $currentSection = 'General';
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) {
                continue;
            }
            
            // Detect section headers
            if (preg_match('/^(SECTION|PART|CHAPTER)\s*[:\d]?\s*(.+)/i', $line, $matches) ||
                preg_match('/^([A-Z][A-Z\s&]+)$/', $line)) {
                $currentSection = $line;
                $structure[] = [
                    'type' => 'section',
                    'content' => $line,
                    'section' => $currentSection
                ];
            }
            // Detect checklist items
            elseif (preg_match('/(□|☐|☑|✓|\[?\s*\]?)\s*(.+)/', $line, $matches)) {
                $structure[] = [
                    'type' => 'checklist_item',
                    'content' => trim($matches[2]),
                    'section' => $currentSection,
                    'has_checkbox' => true
                ];
            }
            // Detect text fields
            elseif (preg_match('/(.+):\s*_+/', $line, $matches)) {
                $structure[] = [
                    'type' => 'text_field',
                    'label' => trim($matches[1]),
                    'section' => $currentSection
                ];
            }
            // Other content
            else {
                $structure[] = [
                    'type' => 'content',
                    'content' => $line,
                    'section' => $currentSection
                ];
            }
        }
        
        return $structure;
    }




    public function readWithPython()
{
    $pdfPath = public_path('Plumbing QC Checklist.pdf');
    $pythonScript = base_path('python/extract_pdf_fields.py'); // مسار سكريبت Python

    // تشغيل السكريبت
    $cmd = escapeshellcmd("python \"$pythonScript\" \"$pdfPath\"");
    $output = shell_exec($cmd);

    if ($output === null) {
        return response()->json(['error' => 'Python script not executed'], 500);
    }

    // إذا السكريبت يرجع JSON، نحوله لمصفوفة PHP
    $data = json_decode($output, true);

    return response()->json($data, 200, [], JSON_PRETTY_PRINT);
}

    // Simple test endpoint to check Python availability
    public function testPython()
    {
        try {
            // Test Python installation
            $pythonVersion = shell_exec('python3 --version 2>&1');
            $pipVersion = shell_exec('pip3 --version 2>&1');
            
            // Test basic Python execution
            $testScript = "import json; print(json.dumps({'test': 'success', 'python': True}))";
            $testOutput = shell_exec('python3 -c "' . $testScript . '" 2>&1');
            $testResult = json_decode($testOutput, true);
            
            return response()->json([
                'python_available' => !empty($pythonVersion),
                'python_version' => trim($pythonVersion ?? 'Not available'),
                'pip_available' => !empty($pipVersion),
                'pip_version' => trim($pipVersion ?? 'Not available'),
                'test_execution' => $testResult ?? ['error' => 'Test failed'],
                'raw_test_output' => $testOutput
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Python test failed: ' . $e->getMessage()
            ], 500);
        }
    }

}