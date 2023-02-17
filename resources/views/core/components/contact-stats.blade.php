@if($contact->reports()->exists())
    @php
        $aperte = $contact->newsletter_stats->aperte;
        $inviate = $contact->newsletter_stats->inviate;
        if($inviate > 0)
        {
            $percent = ($aperte/$inviate)*100;
        }
        else
        {
            $percent = 0;
        }

    @endphp
    @if($percent >= 50)
        <span class="text-success">{{$percent}}% ({{$inviate}})</span>
    @elseif($percent >= 30 && $percent < 50)
        <span class="text-warning">{{$percent}}% ({{$inviate}})</span>
    @else
        <span class="text-danger">{{$percent}}% ({{$inviate}})</span>
    @endif
@else
    <span class="text-muted">0% (0)</span>
@endif
