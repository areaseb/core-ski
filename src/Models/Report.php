<?php

namespace Areaseb\Core\Models;

class Report extends Primitive
{
    protected $guarded = array();
    protected $casts = [
        'opened_at' => 'datetime:Y-m-d H:i:s',
        'clicks' => 'array',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function newsletter()
    {
        return $this->belongsTo(Newsletter::class);
    }

//SCOPES
    public function scopeInviate($query)
    {
        $query = $query->where('delivered', true);
    }

    public function scopeErrore($query)
    {
        $query = $query->whereNotNull('error');
    }

    public function scopeAperte($query)
    {
        $query = $query->where('opened', true);
    }

    public function scopeUnsubscribed($query)
    {
        $query = $query->where('unsubscribed', true);
    }

    public function scopeClicked($query)
    {
        $query = $query->whereNotNull('clicks');
    }

//TRACKING
    public static function identify($value)
    {
        return self::where('identifier', $value)->first();
    }

    public static function stats($newsletter)
    {
        $tot = 0;
        if($newsletter->lists()->where('list_id', 1)->exists())
        {
            $tot = Contact::subscribed()->count();
        }
        else
        {
            foreach ($newsletter->lists as $list)
            {
                $tot += $list->count_contacts;
            }
        }


        $data = [
            'inizio' => self::where('newsletter_id', $newsletter->id)->orderBy('id', 'ASC')->first(),
            'fine' => self::where('newsletter_id', $newsletter->id)->orderBy('id', 'DESC')->first(),
            'totali' => $tot,
            'inviate' => self::where('newsletter_id', $newsletter->id)->inviate()->count(),
            'aperte' => self::where('newsletter_id', $newsletter->id)->aperte()->count(),
            'errore' => self::where('newsletter_id', $newsletter->id)->errore()->count(),
            'unsubscribed' => self::where('newsletter_id', $newsletter->id)->unsubscribed()->count(),
        ];

        return (object)$data;
    }

    public static function clicks($newsletter)
    {
        if($newsletter->reports()->clicked()->exists())
        {
            $numbers = [];
            foreach($newsletter->reports()->clicked()->get() as $report)
            {
                foreach($report->clicks as $click)
                {
                    $totals[] = $click['number'];
                    if(!in_array($click['number'], $numbers))
                    {
                        $numbers[] = $click['number'];
                        $reference[$click['number']] = $click['url'];
                    }
                }
            }
            foreach(array_count_values($totals) as $number => $total)
            {
                $results[] = (object)[
                    'number' => $number,
                    'url' => $reference[$number],
                    'total' => $total
                ];
            }
            return (object)$results;
        }
        return [];
    }


}
