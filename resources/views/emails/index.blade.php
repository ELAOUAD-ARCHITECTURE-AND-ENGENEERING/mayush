<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#f3f4f6">
    @php
    $logo = get_setting('header_logo');
    @endphp
    <tr>
        <td align="center" valign="top" style="padding: 50px 10px;">
            <!-- Container -->
            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 650px; margin: 0 auto;">
                <tr>
                    <td align="center">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                            <tr>
                                <td style="line-height:0pt; padding:0; margin:0; font-weight:normal;">
                                    <!-- Header -->
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #0b60bd; border-bottom: 3px solid #084992;">
                                        <tr>
                                            <td style="padding: 25px 30px;">
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <th style="line-height:0pt; padding:0; margin:0; font-weight:normal; text-align: left;">
                                                            @if($logo)
                                                                <img src="{{ uploaded_asset($logo) }}" height="32" border="0" alt="{{ env('APP_NAME') }}" style="max-height: 32px; display: block;" />
                                                            @else
                                                                <span style="color: #ffffff; font-family: 'Public Sans', sans-serif; font-size: 24px; font-weight: bold;">{{ env('APP_NAME') }}</span>
                                                            @endif
                                                        </th>
                                                        <th width="170" style="line-height:0pt; padding:0; margin:0; font-weight:normal;">
                                                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td style="color:#ffffff; font-family:'Public Sans', sans-serif; font-size:14px; text-align:right;">
                                                                        <a href="{{ env('APP_URL') }}" target="_blank" style="color:#ffffff; text-decoration:none; font-weight: 500;">
                                                                            {{ env('APP_NAME') }}
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </th>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- END Header -->

                                    <!-- Content -->
                                    <div style="padding: 40px 30px; font-family: 'Public Sans', 'Inter', Helvetica, Arial, sans-serif; color: #374151; line-height: 1.6; font-size: 15px;">
                                        {!! $content !!}
                                    </div>
                                    <!-- END Content -->
                                    
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Footer -->
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td style="padding: 20px 30px; text-align: center; font-family: 'Public Sans', 'Inter', Helvetica, Arial, sans-serif; color: #6b7280; font-size: 12px; line-height: 1.5;">
                                    <p style="margin: 0 0 10px 0;">
                                        &copy; {{ date('Y') }} {{ env('APP_NAME') }}. All rights reserved.
                                    </p>
                                    <p style="margin: 0;">
                                        This email was sent to you because you are registered on our platform.<br>
                                        <a href="{{ env('APP_URL') }}" style="color: #0b60bd; text-decoration: none;">Visit our website</a>
                                    </p>
                                </td>
                            </tr>
                        </table>
                        <!-- END Footer -->
                    </td>
                </tr>
            </table>
            <!-- END Container -->
        </td>
    </tr>
</table>