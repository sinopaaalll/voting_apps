<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Kandidat;
use App\Models\Voting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VotingResultsExporter
{
    public function download(): StreamedResponse
    {
        $spreadsheet = $this->workbook();

        return response()->streamDownload(function () use ($spreadsheet): void {
            try {
                (new Xlsx($spreadsheet))->save('php://output');
            } finally {
                $spreadsheet->disconnectWorksheets();
            }
        }, 'hasil-voting.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function workbook(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setCreator(config('app.name'))
            ->setTitle('Hasil Voting')
            ->setSubject('Ringkasan dan detail hasil voting employee');

        $this->summarySheet($spreadsheet);
        $this->voterSheet($spreadsheet);
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function summarySheet(Spreadsheet $spreadsheet): void
    {
        $totalEmployees = Employee::count();
        $totalVotes = Voting::count();
        $candidates = Kandidat::query()->withCount('votes')->orderBy('nomor_urut')->get();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Ringkasan');
        $sheet->setShowGridlines(false);
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', 'HASIL VOTING EMPLOYEE');
        $sheet->fromArray([
            ['Total Employee', $totalEmployees, 'Suara Masuk', $totalVotes],
            ['Belum Memilih', max(0, $totalEmployees - $totalVotes), 'Partisipasi', '=IF(B2=0,0,D2/B2)'],
        ], null, 'A2');
        $sheet->fromArray(['Nomor Urut', 'Nama Kandidat', 'Total Suara', 'Persentase'], null, 'A5');

        $row = 6;
        foreach ($candidates as $candidate) {
            $sheet->setCellValue("A{$row}", $candidate->nomor_urut);
            $sheet->setCellValue("B{$row}", $candidate->name);
            $sheet->setCellValue("C{$row}", $candidate->votes_count);
            $sheet->setCellValue("D{$row}", "=IF(\$D\$2=0,0,C{$row}/\$D\$2)");
            $row++;
        }

        $lastRow = max(5, $row - 1);
        $this->styleTitle($sheet, 'A1:D1');
        $this->styleHeader($sheet, 'A5:D5');
        $sheet->getStyle('A2:D3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F4F7FB');
        $sheet->getStyle('A2:D3')->getFont()->setBold(true);
        $sheet->getStyle('D3')->getNumberFormat()->setFormatCode('0.0%');
        if ($lastRow >= 6) {
            $sheet->getStyle("D6:D{$lastRow}")->getNumberFormat()->setFormatCode('0.0%');
        }
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(34);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(17);
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->freezePane('A6');
        $sheet->setAutoFilter("A5:D{$lastRow}");
    }

    private function voterSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Detail Pemilih');
        $sheet->setShowGridlines(false);
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'DETAIL PILIHAN EMPLOYEE');
        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', 'Data ini bersifat rahasia dan hanya ditujukan untuk admin pemilihan.');
        $headers = [
            'No', 'NIK', 'Nama Employee', 'Department', 'Status', 'Jabatan',
            'Nomor Kandidat', 'Nama Kandidat', 'Waktu Memilih',
        ];
        $sheet->fromArray($headers, null, 'A4');

        $votes = DB::table('voting')
            ->join('employee', 'employee.id', '=', 'voting.employee_id')
            ->join('kandidat', 'kandidat.id', '=', 'voting.kandidat_id')
            ->select([
                'employee.nik',
                'employee.name as employee_name',
                'employee.department',
                'employee.employment_status',
                'employee.position',
                'kandidat.nomor_urut',
                'kandidat.name as kandidat_name',
                'voting.created_at as voted_at',
            ])
            ->orderBy('kandidat.nomor_urut')
            ->orderBy('employee.name')
            ->cursor();

        $row = 5;
        foreach ($votes as $index => $vote) {
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValueExplicit("B{$row}", (string) $vote->nik, DataType::TYPE_STRING);
            $sheet->setCellValue("C{$row}", $vote->employee_name);
            $sheet->setCellValue("D{$row}", $vote->department);
            $sheet->setCellValue("E{$row}", ucfirst($vote->employment_status));
            $sheet->setCellValue("F{$row}", $vote->position);
            $sheet->setCellValue("G{$row}", $vote->nomor_urut);
            $sheet->setCellValue("H{$row}", $vote->kandidat_name);
            $sheet->setCellValue("I{$row}", Date::PHPToExcel(Carbon::parse($vote->voted_at)));
            $row++;
        }

        if ($row === 5) {
            $sheet->mergeCells('A5:I5');
            $sheet->setCellValue('A5', 'Belum ada suara masuk.');
        }

        $lastRow = max(4, $row - 1);
        $this->styleTitle($sheet, 'A1:I1');
        $this->styleHeader($sheet, 'A4:I4');
        $sheet->getStyle('A2:I2')->getFont()->getColor()->setRGB('667085');
        if ($row > 5) {
            $sheet->getStyle("I5:I{$lastRow}")->getNumberFormat()->setFormatCode('yyyy-mm-dd hh:mm');
        }

        $widths = [8, 18, 28, 25, 13, 25, 17, 28, 21];
        foreach (range('A', 'I') as $index => $column) {
            $sheet->getColumnDimension($column)->setWidth($widths[$index]);
        }
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->freezePane('A5');
        $sheet->setAutoFilter("A4:I{$lastRow}");
    }

    private function styleTitle($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '176B87']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
    }

    private function styleHeader($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '172033']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
    }
}
