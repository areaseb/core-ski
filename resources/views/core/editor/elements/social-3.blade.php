<table class="main" width="100%" cellspacing="0" cellpadding="0" border="0" data-types="social-content,background,padding" align="center" data-last-type="social-content" style="background-color:#FFFFFF;">
    <tbody>
        <tr>
            <td class="element-content social-content" style="padding:20px 50px;text-align:center">

                @php
                    $social = Areaseb\Core\Models\Setting::where('model', 'Social')->first();
                @endphp

                @if($social && $social->areFieldsFilled())
                    @foreach($social->fields as $key => $value)

                        @if($value != '')
                            <a href="{{$value}}" style="border: none;display: inline-block;margin-top: 10px;" class="instagram">
                                <img border="0" src="{{asset('editor/assets/images/social-icons/'.$key.'-03.png')}}" width="32">
                            </a>
                        @endif

                    @endforeach
                @else

                    <a href="#insta3" style="border: none;display: inline-block;margin-top: 10px;" class="instagram">
                        <img border="0" alt="instagram" src="{{asset('editor/assets/images/social-icons/instagram-03.png')}}" width="32">
                    </a>
                    <a href="#" style="border: none;display: inline-block;margin-top: 10px;" class="pinterest">
                        <img border="0" alt="pinterest" src="{{asset('editor/assets/images/social-icons/pinterest-03.png')}}" width="32">
                    </a>
                    <a href="#" style="border: none;display: inline-block;margin-top: 10px;" class="facebook">
                        <img border="0" alt="facebook" src="{{asset('editor/assets/images/social-icons/facebook-03.png')}}" width="32">
                    </a>
                    <a href="#" style="border: none;display: inline-block;margin-top: 10px;" class="twitter">
                        <img border="0" alt="twitter" src="{{asset('editor/assets/images/social-icons/twitter-03.png')}}" width="32">
                    </a>
                    <a href="#" style="border: none;display: inline-block;margin-top: 10px;" class="linkedin">
                        <img border="0" alt="linkedin" src="{{asset('editor/assets/images/social-icons/linkedin-03.png')}}" width="32">
                    </a>
                    <a href="#" style="border: none;display: inline-block;margin-top: 10px;" class="youtube">
                        <img border="0" alt="youtube" src="{{asset('editor/assets/images/social-icons/youtube-03.png')}}" width="32">
                    </a>

                @endif
            </td>
        </tr>
    </tbody>
</table>
