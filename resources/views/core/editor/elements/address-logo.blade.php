<table class="main" width="100%" cellspacing="0" cellpadding="0" border="0" data-types="background,padding,image-settings" align="center" data-last-type="background">
    <tbody>
        <tr>
            <td align="left" class="page-header element-content" style="padding-left:50px;padding-right:50px;padding-top:10px;padding-bottom:10px;background-color:#FFFFFF;text-align:center">
                <table width="100%" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="text-align:left" contenteditable="true">
                            @php
                                $img = asset('img/AdminLTELogo.png');
                                if(Areaseb\Core\Models\Setting::where('model', 'Base')->exists())
                                {
                                    $img = Areaseb\Core\Models\Setting::DefaultLogo();
                                }
                                $base = Areaseb\Core\Models\Setting::where('model', 'Base')->first();
                                $homelink = '#';
                                if(isset($base->fields['sitoweb']))
                                {
                                    if($base->fields['sitoweb'] != '')
                                    {
                                        $homelink = 'https://www.'.$base->fields['sitoweb'];
                                    }
                                }
                            @endphp
                            <a href="{{$homelink}}">
                                <img border="0" alt="logo" class="content-image" src="{{$img}}" style="display: inline-block;margin:0px;width:150px;height:auto">
                            </a>
                        </td>
                        <td contenteditable="true" style="text-align:right">
                            {!! $base->getAddress() !!}
                            <a href="%%%unsubscribe%%%">disiscriviti</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </tbody>
</table>
