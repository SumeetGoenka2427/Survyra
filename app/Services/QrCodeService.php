<?php

namespace App\Services;

use App\Models\QrCode as QrCodeModel;
use App\Models\Survey;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;

/**
 * The full, persisted, labeled QR system - Phase 3 only needed an ad-hoc
 * single download for "share this survey right now"; this is the real one
 * (Table 5, Reception Desk, Poster...) per task.md §17.
 */
class QrCodeService
{
    public function __construct(private readonly ShortLinkService $shortLinks)
    {
    }

    public function forSurvey(Survey $survey): Collection
    {
        return QrCodeModel::query()->where('survey_id', $survey->id)->latest()->get();
    }

    /**
     * @param  'svg'|'pdf'  $format
     */
    public function generate(Survey $survey, string $label, string $format = 'svg'): QrCodeModel
    {
        $shortLink = $this->shortLinks->createFor(url("/s/{$survey->slug}"));
        $trackedUrl = url("/l/{$shortLink->code}");

        $svg = QrCodeGenerator::format('svg')->size(400)->generate($trackedUrl);
        $directory = "qr-codes/{$survey->client_id}";

        if ($format === 'pdf') {
            $html = view('pdf.qr-code', ['label' => $label, 'svg' => $svg, 'survey' => $survey])->render();
            $filePath = "{$directory}/".uniqid('qr_', true).'.pdf';
            Storage::disk('public')->put($filePath, Pdf::loadHTML($html)->output());
        } else {
            $filePath = "{$directory}/".uniqid('qr_', true).'.svg';
            Storage::disk('public')->put($filePath, $svg);
        }

        return QrCodeModel::query()->create([
            'client_id' => $survey->client_id,
            'survey_id' => $survey->id,
            'label' => $label,
            'format' => $format,
            'file_path' => $filePath,
            'short_link_id' => $shortLink->id,
        ]);
    }

    public function delete(QrCodeModel $qrCode): void
    {
        Storage::disk('public')->delete($qrCode->file_path);
        $qrCode->delete();
    }
}
