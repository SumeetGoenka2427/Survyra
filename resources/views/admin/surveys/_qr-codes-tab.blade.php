@if ($survey->status !== 'published')
    <div class="alert alert-warning">Publish this survey first to generate QR codes for it.</div>
@else
    <div class="row g-3 mb-4">
        @forelse ($qrCodes as $qrCode)
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-qr-code" style="font-size: 2rem;"></i>
                        <div class="fw-semibold mt-2">{{ $qrCode->label }}</div>
                        <div class="text-muted small mb-2">{{ strtoupper($qrCode->format) }}</div>
                        <a href="{{ route('admin.surveys.qr-codes.download', [$survey, $qrCode]) }}" class="btn btn-sm btn-outline-primary">Download</a>
                        <form action="{{ route('admin.surveys.qr-codes.destroy', [$survey, $qrCode]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this QR code?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-muted text-center py-3">No QR codes generated yet.</div>
        @endforelse
    </div>

    <div class="card border-0 bg-light">
        <div class="card-body">
            <h6 class="mb-3">Generate QR Code</h6>
            <form method="POST" action="{{ route('admin.surveys.qr-codes.store', $survey) }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-6">
                    <label class="form-label">Label</label>
                    <input type="text" name="label" class="form-control" placeholder="e.g. Table 5, Reception Desk, Poster" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Format</label>
                    <select name="format" class="form-select">
                        <option value="svg">SVG (digital)</option>
                        <option value="pdf">PDF (printable)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Generate</button>
                </div>
            </form>
        </div>
    </div>
@endif
