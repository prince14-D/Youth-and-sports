<h2>Interview scheduled</h2>
<p>Hello {{ $interview->application->applicant_name }},</p>
<p>Your interview for <strong>{{ $interview->application->vacancy->title }}</strong> is scheduled for {{ $interview->scheduled_at->format('d M Y, H:i') }}.</p>
<p>Mode: {{ $interview->mode }}@if($interview->location), {{ $interview->location }}@endif</p>