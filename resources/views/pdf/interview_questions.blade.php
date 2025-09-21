<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Natural View Sidemen - Pertanyaan Wawancara</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; margin: 20px; }
        h2 { text-align: center; color: darkblue; margin-bottom: 40px; }
        h3 { color: darkred; margin-bottom: 0px; }
        p { margin: 5px 0 0px 0; }
        ul { margin: 0px 0 0px -25px; }
        .section-title { font-weight: bold; margin-top: 5px; margin-bottom: 10px; }
        .salary { font-weight: bold; color: green; margin-bottom: 5px; }
        .page-break { page-break-after: always; }
        
        /* New styles for better formatting of questions and guidelines */
        .question-block { margin-bottom: 10px; }
        .guidelines { font-style: italic; font-size: 12px; margin-top: 5px; }
        .guidelines .good { color: green; }
        .guidelines .poor { color: #d78b1c; }
        .guidelines .red_flag { color: red; }
    </style>
</head>
<body>
    <!-- <h2>New Natural View Sidemen - Pertanyaan Wawancara</h2> -->

    <!-- Loop through the main interview questions data -->
    @foreach($data['interview_questions'] as $position)
        <!-- Apply a page break for each new position title -->
        <div class="page-break">
            <h3>{{ $position['title'] }}</h3>
            
            <!-- Loop through the questions for the current position -->
            <div class="section-title">Pertanyaan:</div>
            <ul>
            @foreach($position['questions'] as $question)
                <li>
                    <div class="question-block">
                        <p style="font-size: 14px;">{{ $question['question'] }}</p>
                        
                        <!-- Display answer guidelines, if they exist -->
                        @if(isset($question['answer_guidelines']))
                            <div class="guidelines">
                                <p class="good">Baik: {{ $question['answer_guidelines']['good'] }}</p>
                                <p class="poor">Buruk: {{ $question['answer_guidelines']['poor'] }}</p>
                                <p class="red_flag">Red Flag: {{ $question['answer_guidelines']['red_flag'] }}</p>
                            </div>
                        @endif
                    </div>
                </li>
            @endforeach
            </ul>
        </div>
    @endforeach
</body>
</html>
