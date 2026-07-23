@extends('layouts.contentNavbarLayout')

@section('title', 'QR Code — ' . $bien->code_interne)

@section('content')
    <a href="{{ route('immobilisations.biens.show', $bien->id) }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <div class="card mx-auto" style="max-width: 400px;">
        <div class="card-body text-center">
            <h5>{{ $bien->code_interne }}</h5>
            <p class="text-muted">{{ $bien->designation }}</p>
            @php
                $options = new \chillerlan\QRCode\QROptions(['outputType' => \chillerlan\QRCode\Output\QRMarkupSVG::class, 'svgViewBoxSize' => 250]);
                $qrSvg = (new \chillerlan\QRCode\QRCode($options))->render($url);
            @endphp
            <div class="my-3">{!! $qrSvg !!}</div>
            <p class="small text-muted">{{ $url }}</p>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="ti ti-printer"></i> Imprimer
            </button>
        </div>
    </div>
@endsection
