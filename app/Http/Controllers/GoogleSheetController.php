<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleSheetService;

class GoogleSheetController extends Controller
{
    protected $googleSheetService;

    public function __construct(GoogleSheetService $googleSheetService)
    {
        $this->googleSheetService = $googleSheetService;
    }

    public function readGoogleSheet()
    {
        // ID của Google Sheet
        $spreadsheetId = '1rMoO03hR97WX0gFhqwrg8RHDFXeCeAFOTthP0HFzUrY';
        // Phạm vi muốn đọc 
        $range = 'KQDG - FA24!A1:AV49';

        try {
            $values = $this->googleSheetService->readSheet($spreadsheetId, $range);

            if (empty($values)) {
                return response()->json(['message' => 'Không thấy dư liệu'], 404);
            }

            return response()->json(['data' => $values], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
