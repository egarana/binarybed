<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Natural View Sidemen - Lowongan Kerja</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; margin: 20px; }
        h2 { text-align: center; color: darkblue; margin-bottom: 40px; }
        h3 { color: darkred; margin-bottom: 0px; }
        p { margin: 5px 0 0px 0; }
        ul { margin: 5px 0 10px 20px; }
        .section-title { font-weight: bold; margin-top: 5px; }
        .salary { font-weight: bold; color: green; margin-bottom: 5px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <h2>New Natural View Sidemen - Lowongan Kerja</h2>

    @foreach($vacancies['jobs'] as $index => $job)
        <h3>{{ $loop->iteration }}. {{ $job['title'] }}</h3>
        <p><em>{{ $job['summary'] }}</em></p>

        @if(!empty($job['salary_range']))
            <p class="salary">Gaji: {{ $job['salary_range'] }}</p>
        @endif

        @if(!empty($job['apply_link']))
            <p>Detail: <a target="_blank" href="{{ $job['apply_link'] }}">{{ $job['apply_link'] }}</a></p>
        @endif

        <!-- <div class="section-title">Tugas dan Tanggung Jawab:</div>
        <ul>
            @foreach($job['responsibilities'] as $resp)
                <li>{{ $resp }}</li>
            @endforeach
        </ul>

        <div class="section-title">Persyaratan:</div>
        <ul>
            @foreach($job['requirements'] as $req)
                <li>{{ $req }}</li>
            @endforeach
        </ul> -->
    @endforeach
</body>
</html>
