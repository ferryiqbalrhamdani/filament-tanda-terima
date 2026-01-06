<?php

namespace App\Filament\Resources\LetterResource\Pages;

use App\Filament\Resources\LetterResource;
use App\Models\Letter;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateLetter extends CreateRecord
{
    protected static string $resource = LetterResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $company = $data['company_id'];
        $tanggal = Carbon::parse($data['tanggal_surat']);
        $bulanRomawi = $this->convertToRoman($tanggal->month);
        $tahun = $tanggal->year;

        // Ambil slug dari tabel company berdasarkan company_id
        $companySlug = DB::table('companies')
            ->where('id', $company)
            ->value('slug');

        if (!$companySlug) {
            throw new \Exception("Company dengan ID {$company} tidak ditemukan.");
        }

        // Ambil nomor terakhir untuk perusahaan ini
        $lastNumberCompany = $this->getLastNumberForCompanyActual($company, $tanggal);
        $lastNumber = $this->getLastNumberForCompany($company, $tanggal);

        if ($lastNumberCompany == $lastNumber) {
            $counter = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            $nomorSurat = "{$counter}/{$companySlug}/{$bulanRomawi}/{$tahun}";
        } else {
            $nomorSurat = $this->generateLetterNumber($company, $tanggal, $lastNumber, $companySlug);
        }

        $data['letter_number'] = $nomorSurat;

        return $data;
    }

    protected function afterCreate(): void
    {
        $data = $this->data;

        $jumlahSurat = $data['jumlah_surat'];
        $company = $data['company_id'];
        $pic = $data['pic_id'];
        $title = $data['title'];
        $content = $data['content'];
        $tanggal = Carbon::parse($data['tanggal_surat']);
        $bulanRomawi = $this->convertToRoman($tanggal->month);
        $tahun = $tanggal->year;

        $companySlug = DB::table('companies')
            ->where('id', $company)
            ->value('slug');

        $lastNumberCompany = $this->getLastNumberForCompanyActual($company, $tanggal);
        $lastNumber = $this->getLastNumberForCompany($company, $tanggal);

        if ($jumlahSurat > 1) {
            $counter = $lastNumber + 1;
            for ($i = 1; $i < $jumlahSurat; $i++) {
                $nomorSurat = str_pad($counter, 3, '0', STR_PAD_LEFT) . "/{$companySlug}/{$bulanRomawi}/{$tahun}";

                sleep(1);

                Letter::create([
                    'letter_number' => $nomorSurat,
                    'company_id' => $company,
                    'pic_id' => $pic,
                    'tanggal_surat' => $tanggal,
                    'title' => $title,
                    'content' => $content,
                ]);

                $counter++;
            }
        }
    }

    private function convertToRoman($month): string
    {
        $romans = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        return $romans[$month - 1];
    }

   private function getLastNumberForCompany($company, $tanggal): int
    {
        return DB::table('letters')
            ->where('company_id', $company)
            ->whereYear('tanggal_surat', $tanggal->year) // 🔥 reset per tahun
            ->max(DB::raw("
                CAST(
                    regexp_replace(SPLIT_PART(letter_number, '/', 1), '[^0-9]', '', 'g')
                AS INTEGER)
            ")) ?? 0;
    }

    private function getLastNumberForCompanyActual($company, $tanggal): int
    {
        return DB::table('letters')
            ->where('company_id', $company)
            ->whereYear('tanggal_surat', $tanggal->year)
            ->max(DB::raw("
                CAST(
                    regexp_replace(SPLIT_PART(letter_number, '/', 1), '[^0-9]', '', 'g')
                AS INTEGER)
            ")) ?? 0;
    }

    private function generateLetterNumber($company, $tanggal, $lastNumber, $companySlug): string
    {
        $bulanRomawi = $this->convertToRoman($tanggal->month);
        $tahun = $tanggal->year;

        $existingLetters = DB::table('letters')
            ->where('company_id', $company)
            ->whereDate('tanggal_surat', $tanggal)
            ->whereRaw("letter_number ~ '^" . str_pad($lastNumber, 3, '0', STR_PAD_LEFT) . "[A-Z]?/.*'")
            ->pluck(DB::raw("SPLIT_PART(letter_number, '/', 1) AS letter_part"));

        $alphabet = range('A', 'Z');
        $newLetter = '';

        foreach ($alphabet as $letter) {
            if (!$existingLetters->contains(str_pad($lastNumber, 3, '0', STR_PAD_LEFT) . $letter)) {
                $newLetter = $letter;
                break;
            }
        }

        $counter = str_pad($lastNumber, 3, '0', STR_PAD_LEFT) . $newLetter;
        return "{$counter}/{$companySlug}/{$bulanRomawi}/{$tahun}";
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl;
    }
}
