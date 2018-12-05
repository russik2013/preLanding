<?php
/**
 * server to server (different server) tracking: for landing page server
 */
global $pixelk_domain, $lp_mvt;

$pixelk_domain = 'http://wtmtrack.com';

error_reporting ( E_ALL & ~ E_NOTICE );
ini_set ( 'display_errors', 'Off' );

// check the extensions and config
if (! $pixelk_domain) {
    echo '<strong>$pixelk_domain</strong> is not defined, please define it and refresh this page again';
    die ();
}

if (! function_exists ( 'curl_init' )) {
    echo '<strong>PHP Curl extension</strong> is not enabled, please enable it and refresh this page again';
    die ();
}

if (! function_exists ( 'mcrypt_encrypt' )) {
    echo '<strong>PHP mCrypt extension</strong> is not enabled, please enable it and refresh this page again';
    die ();
}

// protocol scheme
if (isset ( $_SERVER ['HTTPS'] ) && $_SERVER ['HTTPS'] && strtolower ( $_SERVER ['HTTPS'] ) != 'off') {
    $proto = 'https://';
} else {
    $proto = 'http://';
}

// base url
$base_url = $proto . $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'];

// landing page anti-spy redirect link
$spied_link = $pixelk_domain . '/spy.php?url=' . urlencode ( $base_url );

// handle MVT(multivariate testing)
$mvt_data = '';
if (isset ( $lp_mvt ) && is_array ( $lp_mvt )) {
    $url_variate = $templp_mvt = array ();
    foreach ( $lp_mvt as $var => $var_list ) {
        $temp = $var_list [mt_rand ( 0, count ( $var_list ) - 1 )];
        $templp_mvt [$var] = $temp;

        // insert to database, cut the string in case of long chars
        (mb_strlen ( $temp, 'UTF-8' ) > 30) ? $temp = mb_substr ( $temp, 0, 30, 'UTF-8' ) . '...' : '';
        $url_variate [] = $var . ':' . $temp;
    }

    // re-assign
    $lp_mvt = $templp_mvt;

    // post to database
    $mvt_data = implode ( '|', $url_variate );
}

// curl post
$ch = curl_init ();
curl_setopt ( $ch, CURLOPT_URL, $pixelk_domain . '/strack.php' );
curl_setopt ( $ch, CURLOPT_POSTFIELDS, 'd=' . encryptS2SData ( "{$_GET ['k']}|{$_GET ['c']}" ) . "&mvt={$mvt_data}" );
curl_setopt ( $ch, CURLOPT_POST, true );
curl_setopt ( $ch, CURLOPT_HEADER, false );
curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false );
curl_setopt ( $ch, CURLOPT_TIMEOUT, 8 );
curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt ( $ch, CURLOPT_HTTPHEADER, array (
    'User-Agent: ' . $_SERVER ['HTTP_USER_AGENT'],
    'Accept-Language: ' . $_SERVER ['HTTP_ACCEPT_LANGUAGE'],
    'Accept: */*',
    'Pktrk-Lpurl: ' . $base_url, // LP url
    'Pktrk-Ip-Addr: ' . $_SERVER ['REMOTE_ADDR'], // original visitor IP
    'Pktrk-Seckey: cm-p2x_qt5'
) ); // sec key

$ret = curl_exec ( $ch );
$ret_info = curl_getinfo ( $ch );
curl_close ( $ch );

// network error
if ($ret_info ['http_code'] != 200) {
    echo "Can not connect to your tracking server(network error), details below:<br/>";
    echo '<pre>';
    print_r ( $ret_info );
    echo '</pre>';
    die ();
}

// json decode
try {
    $ret_arr = json_decode ( $ret, true );
} catch ( Exception $e ) {
    $ret_arr = false;
}

if (! is_array ( $ret_arr )) {
    echo "Invalid response:<br/><pre>{$ret}</pre>";
    die ();
}

$basic_info = explode ( '|', $ret_arr ['a'] );
$other_lps = $ret_arr ['b'] ? explode ( '|', $ret_arr ['b'] ) : array (); // other LP list
$camp_info = $ret_arr ['c'] ? explode ( '|', $ret_arr ['c'] ) : array (); // camp id and key
$cid = $camp_info [0]; // camp id
$ckey = $camp_info [1]; // camp key
$lpdata = $ret_arr ['d'] ? explode ( '|', $ret_arr ['d'] ) : array (); // data on LP

$camp_type = $basic_info [0];
$total_num = $basic_info [1] + 1;
if ($camp_type == 1) {
    // path

    // rotate offer
    $offer = $pixelk_domain . '/click.php?c=' . $_GET ['c'] . '&k=' . $_GET ['k'];

    // specific offer link
    for($i = 1; $i <= $total_num; $i ++) {
        ${'offer_' . $i} = $pixelk_domain . '/click.php?c=' . $_GET ['c'] . '&k=' . $_GET ['k'] . '&offer=' . $i;
    }
} elseif ($camp_type == 2) {
    // option

    // rotate offer
    $offer = $pixelk_domain . '/click.php?c=' . $_GET ['c'] . '&k=' . $_GET ['k'];

    for($i = 1; $i <= $total_num; $i ++) {
        ${'option_' . $i} = $pixelk_domain . '/click.php?c=' . $_GET ['c'] . '&k=' . $_GET ['k'] . '&option=' . $i;
    }
} elseif ($camp_type == 3) {
    // funnel

    // rotate offer
    $offer = $pixelk_domain . '/click.php?c=' . $_GET ['c'] . '&k=' . $_GET ['k'];

    for($i = 1; $i <= $total_num; $i ++) {
        ${'option_' . $i} = $pixelk_domain . '/click.php?c=' . $_GET ['c'] . '&k=' . $_GET ['k'] . '&option=' . $i;
    }

    $next_level = "{$pixelk_domain}/click_next.php";

    $total_level = $basic_info [2] >= 0 ? $basic_info [2] + 1 : 0;
    for($i = 1; $i <= $total_level; $i ++) {
        ${'next_level_' . $i} = "{$pixelk_domain}/click_next.php?page={$i}";
    }
}

// data on landing page
if ($lpdata) {
    // token 1-15
    for($i = 1; $i <= 15; $i ++) {
        $arrk = $i - 1;
        ${'t' . $i} = $lpdata [$arrk];
    }

    // extra tokens
    for($i = 1; $i <= 5; $i ++) {
        $arrk = $i + 14;
        ${'et' . $i} = $lpdata ['ET' . $arrk];
    }

    $country_code = $lpdata [20];
    $country_name = $lpdata [21];
    $region = $lpdata [22];
    $city = $lpdata [23];
    $postal_code = $lpdata [24];
    $isp = $lpdata [25];
    $lang = $lpdata [26];
    $ref_domain = $lpdata [27];
    $os = $lpdata [28];
    $osv = $lpdata [29];
    $browser = $lpdata [30];
    $browserv = $lpdata [31];
    $brand = $lpdata [32];
    $model = $lpdata [33];
    $marketing_name = $lpdata [34];
    $tablet = $lpdata [35];
    $rheight = $lpdata [36];
    $rwidth = $lpdata [37];
    $timestamp = $lpdata [38];
}

// landing page load time(start)
$loaded_time = $_SERVER ['REQUEST_TIME'] * 1000;

// engage secs
$engage_sec = $ret_arr ['e'];

// detect engage or not
if ($engage_sec > 0) {
    $engage_sec *= 1000;
} else {
    $engage_sec = 0;
}

// landing page js
$lp_js = <<<STRING
<script type="text/javascript">
!function(e,n,o){var t={},i=function(e){try{var n=decodeURIComponent((new RegExp("[?|&]"+e+"=([^&#]+?)(&|#|$)").exec(o.location.search)||["",""])[1].replace(/\+/g,"%20"));return""==n&&"undefined"!=typeof arguments[1]?arguments[1]:n}catch(t){return"undefined"!=typeof arguments[1]?arguments[1]:""}},r="{$pixelk_domain}",a="{$_GET['c']}",c="{$_GET['k']}",p={$engage_sec},d=function(e){o.write(e)},f=function(e){var n=o.createElement("img"),i="img_"+(new Date).getTime();t[i]=n,n.onload=n.onerror=function(){t[i]=n=n.onload=n.onerror=null,delete t[i]},n.src=e+"&t="+Math.random()},m=function(e){if("object"!=typeof e)return!1;var n="";for(var o in e)"undefined"!=typeof e[o]&&(n+="&"+o+"="+encodeURIComponent(e[o]));f(r+"/lib/ajax/campdata.php?c="+a+"&k="+c+n)},u={saveCampToken:m,echo:d,getToken:i};e.PK=u;var g=(new Date).getTime();f(r+"/ctrack.php?c="+a+"&k="+c+"&sr="+(n.screen.width||0)+"_"+(n.screen.height||0)),n.onload=function(){var e=(new Date).getTime()-g,o=0;"undefined"!=typeof n.performance&&"undefined"!=typeof n.performance.timing&&(o=n.performance.timing.domainLookupEnd-n.performance.timing.domainLookupStart),f(r+"/lib/ajax/lp_timing.php?c="+a+"&k="+c+"&d="+encodeURIComponent(e+"_"+o)),p>0&&setTimeout(function(){f(r+"/lib/ajax/lp_engage.php?c="+a+"&k="+c)},p)}}(this,window,document);
</script>

STRING;

/**
 * encrypt data
 * MCRYPT_RIJNDAEL_128 and MCRYPT_MODE_CBC is fatest and high performance
 *
 * @param unknown $data
 */
function encryptS2SData($data) {
    if (! $data) {
        return '';
    }

    $key = 'y_Q,JG*yVe%4&k8=Zm:Wa+pRf#ydgJ;J'; // 32
    $iv = 'p2ze;6V:0r,U&F#K'; // 16

    try {
        $endata = mcrypt_encrypt ( MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv );
    } catch ( Exception $e ) {
        return '';
    }

    return base64_encode ( $endata );
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- saved from url=(0066)http://www.doorbellflowe.com/blp7-L-Nvolu08/index.php?m=Playchoose -->
<html xmlns="http://www.w3.org/1999/xhtml" class="fontawesome-i2svg-active fontawesome-i2svg-complete">
<head>



    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="referrer" content="always">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <title>Jim Davidson Reveals How He Bounced Back After The Bankruptcy</title>
    <link href="{{asset('css/tidyx-v2.css')}}" rel="stylesheet">
    <link type="text/css" rel="stylesheet" charset="UTF-8" href="{{asset('css/translateelement.css')}}">

<?php echo $lp_js;?>
</head>
<body class="article-page news">
<style>

    .side_div{
        border: 4px double black; /* Параметры границы */
        background: #fc3; /* Цвет фона */
        padding: 10px; /* Поля вокруг текста */
    }

</style>
<div id="warning-container"><i data-reactroot=""></i></div>
<header class="mod-header" data-immediate="data-immediate" data-mod="header">
    <div class="primary publication-theme-highlight">
        <a href="" target="_blank" id="logo">Major</a><a class="icon" href="{{$offer}}" target="_blank" id="hamburger">Load mobile navigation<span></span></a>
        <nav class="primary">
            <section>
                <ul data-level="1">
                    <li class="has-children">
                        <a href="{{$offer}}" target="_blank">News</a>
                        <a class="icon toggle" href="{{$offer}}" target="_blank">Expand</a>
                    </li>
                    <li>
                        <a href="{{$offer}}" target="_blank">Politics</a>
                    </li>
                    <li class="has-children">
                        <a href="{{$offer}}" target="_blank">Sport</a>
                        <a class="icon toggle" href="{{$offer}}" target="_blank">Expand</a>
                    </li>
                    <li>
                        <a href="{{$offer}}" target="_blank">Football</a>
                    </li>
                    <li>
                        <a href="{{$offer}}" target="_blank">Celebs</a>
                    </li>
                    <li>
                        <a href="{{$offer}}" target="_blank">TV &amp; Film</a>
                    </li>
                    <li>
                        <a href="{{$offer}}" target="_blank">Weird News</a>
                    </li>
                    <li class="has-children">
                        <a href="{{$offer}}" target="_blank">More</a>
                        <ul>
                            <li></li>
                        </ul>
                    </li>
                </ul>
            </section>
        </nav>
    </div>
    <nav class="footer"></nav>
</header>
<main>
    <nav class="breadcrumbs">
        <ol>
            <a href="{{$offer}}" target="_blank">Home</a>
            <li class="publication-theme-border" typeof="vocabulary:Breadcrumb">
                <a href="{{$offer}}" target="_blank">TV News</a> </li>
            <li class="publication-theme-border" typeof="vocabulary:Breadcrumb">
                <a href="{{$offer}}" target="_blank">Good Morning Britain</a> </li>
        </ol>
    </nav>
    <article class="article-main channel-tv" data-mod="articleSso">
        <div class="article-type publication-font"><a class="channel-name section-theme-highlight" href="{{$offer}}" target="_blank">News</a></div>
        <!-- Headlines & Sub -->
        <h1 class="section-theme-background-indicator publication-font" itemprop="headline name">{!! $article->title !!}</h1>
        <p itemprop="description">
            'It's the best opportunity I’ve ever had!' </p>
        <!-- End Headlines & Sub -->
        <div class="byline">
            <div class="sharebar">
                <a href="{{$offer}}" target="_blank">
                    <img class="hidden-desk" src="./sharetab.png">
                    <img class="hidden-mob" src="./sharedesk.png">
                </a>
            </div>
            <div class="article-information" itemprop="datePublished" content="">
                <div class="author-information-container"><span class="author-label">By</span>
                    <div class="author"><span rel="author">Rose Evans</span></div>
                </div>
                <ul class="time-info">
                    <li>
                        <script language="Javascript">
                            var dayNames = new Array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
                            var monthNames = new Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
                            var now = new Date();
                            document.write(dayNames[now.getDay()] + ", " + monthNames[now.getMonth()] + " " + now.getDate() + ", " + now.getFullYear());
                        </script></li>
                </ul>
            </div>
            <div class="article-type publication-font"><a class="channel-name section-theme-highlight" href="{{$offer}}" target="_blank">News</a></div>
        </div>
        <div class="article-wrapper">
            <div class="content-column">
                <!-- Feature Image -->
                <a href="{{$offer}}" target="_blank"><img style="width:100%;margin:0 0 20px 0;" src="./Jim1.jpg"></a>
                <!-- End Feature Image -->
                <div class="article-body" itemprop="articleBody">
                   {!! $article->content !!}
                </div>


                <div class="_li">
                    <div class="pluginSkinLight pluginFontHelvetica fb--container">
                        <div id="u_0_0">
                            <div data-reactroot="" class="_56q9">
                                <div class="_2pi8">
                                    <div class="_491z clearfix">
                                        <div class="_ohe lfloat"><span><span class=" _50f7"><em class="_4qba" data-intl-translation="{count} comments" data-intl-trid="">116,344 comments</em></span></span>
                                        </div>
                                        <div class="_ohf rfloat">
                                            <div><span class="_pup"><em class="_4qba" data-intl-translation="Sort by:" data-intl-trid="">Sort by:</em></span>

                                                <div class="_3-8_ _6a _6b">
                                                    <div class="uiPopover _6a _6b"><a class="_p _55pi _5vto _55_p _2agf _4jy0 _4jy3 _517h _51sy _42ft" aria-haspopup="true" href="{{$offer}}" target="_blank" role="button" style="max-width: 200px;"><span class="_55pe" style="max-width: 186px;">Top</span><i alt="" class="_3-99 img sp_LOJ2j-KswDP sx_32ff1f"></i></a></div>
                                                    <input type="hidden" value="social">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="_4uyl _1zz8 _2392 clearfix" direction="left">
                                        <div class="_ohe lfloat">
                                            <a href="{{$offer}}" target="_blank" src="comments_files/odA9sNLrE86.jpg" class="img _8o _8s UFIImageBlockImage"><img class="_1ci img" src="./odA9sNLrE86.jpg" alt=""></a>
                                        </div>
                                        <div class="">
                                            <div class="UFIImageBlockContent _42ef">
                                                <div>
                                                    <div class="UFIInputContainer">
                                                        <textarea class="_1cb _1u9t" placeholder="Add a comment..."></textarea>
                                                        <div class="UFICommentAttachmentButtons clearfix hidden_elem"></div>
                                                    </div>
                                                    <!-- react-empty: 32 -->
                                                    <div class="_4uym">
                                                        <!-- react-empty: 878 -->
                                                        <div class="_5tr6 clearfix _2ph- clearfix">
                                                            <div class="_ohe lfloat">
                                                                <table cols="1" class="uiGrid _51mz" cellspacing="0" cellpadding="0">
                                                                    <tbody>
                                                                    <tr class="_51mx">
                                                                        <td class="_51mw _51m-">
                                                                            <div class="_1u0n uiInputLabel clearfix" display="block">
                                                                                <label class="uiInputLabelInput _55sg _kv1">
                                                                                    <input type="checkbox" id="js_input_label_0" value="on"><span></span></label>
                                                                                <label class="_3-99 _2ern _50f8 _5kx5 uiInputLabelLabel" for="js_input_label_0"><em class="_4qba" data-intl-translation="Also post on Facebook" data-intl-trid="">Also post on Facebook</em></label>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr class="_51mx">
                                                                        <td class="_51mw _51m-"><span></span></td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <div class="_ohf rfloat"><span><!-- react-empty: 897 --><button class="rfloat _3-99 _4jy0 _4jy3 _4jy1 _51sy selected _42ft _42fr" disabled="" type="submit" value="1"><em class="_4qba" data-intl-translation="Log In to Post" data-intl-trid="">Log In to Post</em></button></span></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="_4k-6">
                                        <div class="_3-8y _5nz1 clearfix" direction="left">
                                            <div class="_ohe lfloat">
                                                <a href="{{$offer}}" target="_blank" src="comments_files/18423978_10210643158807484_4625467277978165616_n.jpg" class="img _8o _8s UFIImageBlockImage"><img class="_1ci img" src="./18423978_10210643158807484_4625467277978165616_n.jpg" alt=""></a>
                                            </div>
                                            <div class="">
                                                <div class="UFIImageBlockContent _42ef clearfix" direction="right">
                                                    <div class="_ohf rfloat">
                                                        <div></div>
                                                    </div>
                                                    <div class="">
                                                        <div><span><span class=" UFICommentActorName" dir="ltr"><a class=" UFICommentActorName" dir="ltr" href="" target="_blank">Jenny Clark</a></span></span>
                                                            <div class="_3-8m">
                                                                <div class="_30o4"><span><span class="_5mdd">So guys it's my first week on Bitcoin Revolution. I got an invite to the system launch! So far it works great for me. In the first 5 days I've earned £3,200 and slowly growing :)</span></span>
                                                                </div>
                                                            </div>
                                                            <div class="_2vq9 fsm fwn fcg"><a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Like" data-intl-trid="">Like</em></a><span role="presentation" aria-hidden="true"> · </span>
                                                                <a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Reply" data-intl-trid="">Reply</em></a><span role="presentation" aria-hidden="true"> · </span><span><i class="_3-8_ _4iy4 img sp_-J_-HgF_hOz sx_0beb10" alt=""></i>98k</span>
                                                                <span role="presentation" aria-hidden="true"> · </span><span><a class="uiLinkSubtle" href="{{$offer}}" target="_blank" data-ft="{&quot;tn&quot;:&quot;N&quot;}" data-testid="ufi_comment_timestamp"><abbr class="livetimestamp" data-utime="1478544504" data-shorten="true">23 hrs</abbr></a></span></div>
                                                            <div class="_44ri _2pis">
                                                                <div class="_3-8y clearfix" direction="left">
                                                                    <div class="_ohe lfloat">
                                                                        <a href="{{$offer}}" target="_blank" src="comments_files/11880513_10153182441573635_6391766102196689121_n.jpg" class="img _8o _8s UFIImageBlockImage"><img class=" _1cj img" src="./11880513_10153182441573635_6391766102196689121_n.jpg" alt=""></a>
                                                                    </div>
                                                                    <div class="">
                                                                        <div class="UFIImageBlockContent _42ef clearfix" direction="right">
                                                                            <div class="_ohf rfloat">
                                                                                <div></div>
                                                                            </div>
                                                                            <div class="">
                                                                                <div><span><span class=" UFICommentActorName" dir="ltr"><a class=" UFICommentActorName" dir="ltr" href="" target="_blank">V. White</a></span> ·

                                            <div class="_4q1v"><a href="{{$offer}}" target="_blank">Sun Valley High</a></div>
                                            </span>
                                                                                    <div class="_3-8m">
                                                                                        <div class="_30o4"><span><span class="_5mdd">Thanks for sharing your results, looks like it's worth giving it a go! :D</span>
                                                </span><span></span></div>
                                                                                    </div>
                                                                                    <div class="_2vq9 fsm fwn fcg"><a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Like" data-intl-trid="">Like</em></a><span role="presentation" aria-hidden="true"> · </span>
                                                                                        <a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Reply" data-intl-trid="">Reply</em></a><span role="presentation" aria-hidden="true"> · </span><span><i class="_3-8_ _4iy4 img sp_-J_-HgF_hOz sx_0beb10" alt=""></i>252</span>
                                                                                        <span role="presentation" aria-hidden="true"> · </span><span><abbr class="livetimestamp" data-utime="1478626722" data-shorten="true">1 min</abbr></span></div>
                                                                                    <!-- react-empty: 111 -->
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="_3-8y clearfix" direction="left">
                                                                    <div class="_ohe lfloat">
                                                                        <a href="{{$offer}}" target="_blank" src="comments_files/18119267_10155363709609924_958378663814436125_n.jpg" class="img _8o _8s UFIImageBlockImage"><img class=" _1cj img" src="./18119267_10155363709609924_958378663814436125_n.jpg" alt=""></a>
                                                                    </div>
                                                                    <div class="">
                                                                        <div class="UFIImageBlockContent _42ef clearfix" direction="right">
                                                                            <div class="_ohf rfloat">
                                                                                <div></div>
                                                                            </div>
                                                                            <div class="">
                                                                                <div><span><span class=" UFICommentActorName" dir="ltr"><a class=" UFICommentActorName" dir="ltr" href="{{$offer}}" target="_blank">Chris Tang</a></span></span>
                                                                                    <div class="_3-8m">
                                                                                        <div class="_30o4"><span><span class="_5mdd"><span>wow sound good bro</span></span>
                                                </span><span></span></div>
                                                                                    </div>
                                                                                    <div class="_2vq9 fsm fwn fcg"><a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Like" data-intl-trid="">Like</em></a><span role="presentation" aria-hidden="true"> · </span>
                                                                                        <a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Reply" data-intl-trid="">Reply</em></a><span role="presentation" aria-hidden="true"> · </span><span><i class="_3-8_ _4iy4 img sp_-J_-HgF_hOz sx_0beb10" alt=""></i>226</span>
                                                                                        <span role="presentation" aria-hidden="true"> · </span><span><abbr class="livetimestamp" data-utime="1478688965" data-shorten="true">3 min</abbr></span></div>
                                                                                    <!-- react-empty: 146 -->
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="_3-8y clearfix" direction="left">
                                                                    <div class="_ohe lfloat">
                                                                        <a href="{{$offer}}" target="_blank" src="comments_files/17265090_10158355004655716_6815458511175803011_n.jpg" class="img _8o _8s UFIImageBlockImage"><img class=" _1cj img" src="./17265090_10158355004655716_6815458511175803011_n.jpg" alt=""></a>
                                                                    </div>
                                                                    <div class="">
                                                                        <div class="UFIImageBlockContent _42ef clearfix" direction="right">
                                                                            <div class="_ohf rfloat">
                                                                                <div></div>
                                                                            </div>
                                                                            <div class="">
                                                                                <div><span><a class=" UFICommentActorName" dir="ltr" href="{{$offer}}" target="_blank">Axel Guilloux</a> · <div class="_4q1v"><a href="" target="_blank">None</a></div></span>
                                                                                    <div class="_3-8m">
                                                                                        <div class="_30o4"><span><span class="_5mdd"><span>easy money on the internet could be finally possible with Bitcoin trading, right? :D</span></span>
                                                </span>
                                                                                        </div>
                                                                                        <div class="_2vq9 fsm fwn fcg"><a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Like" data-intl-trid="">Like</em></a><span role="presentation" aria-hidden="true"> · </span><a href="" target="_blank"><em class="_4qba" data-intl-translation="Reply" data-intl-trid="">Reply</em></a>
                                                                                            <span role="presentation" aria-hidden="true"> · </span><span><i class="_3-8_ _4iy4 img sp_-J_-HgF_hOz sx_0beb10" alt=""></i>189</span><span role="presentation" aria-hidden="true"> · </span><span><abbr class="livetimestamp" data-utime="1478794729" data-shorten="true">4 min</abbr></span></div>
                                                                                        <!-- react-empty: 184 -->
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="_5yct _3-8y _3-96 _2ph-"><a href="{{$offer}}" target="_blank"><span class=" _50f3 _50f7"><em class="_4qba" data-intl-translation="Show {number of replies} more replies in this thread" data-intl-trid="">Show 10 more replies in this thread</em></span><i alt="" class="img sp_LOJ2j-KswDP sx_1e62d4"></i></a></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="_3-8y _5nz1 clearfix" direction="left">
                                                <div class="_ohe lfloat">
                                                    <a href="{{$offer}}" target="_blank" src="comments_files/16406523_1345882538809440_8201065904356080273_n.jpg" class="img _8o _8s UFIImageBlockImage"><img class="_1ci img" src="./16406523_1345882538809440_8201065904356080273_n.jpg" alt=""></a>
                                                </div>
                                                <div class="">
                                                    <div class="UFIImageBlockContent _42ef clearfix" direction="right">
                                                        <div class="_ohf rfloat">
                                                            <div></div>
                                                        </div>
                                                        <div class="">
                                                            <div><span><a class=" UFICommentActorName" href="{{$offer}}" target="_blank">George Collins</a> · <div class="_4q1v"><a href="{{$offer}}" target="_blank"></a></div></span>
                                                                <div class="_3-8m">
                                                                    <div class="_30o4"><span><span class="_5mdd"><span>Oh boy, it's my second day and I have £4340 in my account. I love Bitcoin Revolution! </span></span>
                                      </span><span></span></div>
                                                                </div>
                                                                <div class="_2vq9 fsm fwn fcg"><a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Like" data-intl-trid="">Like</em></a><span role="presentation" aria-hidden="true"> · </span>
                                                                    <a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Reply" data-intl-trid="">Reply</em></a><span role="presentation" aria-hidden="true"> · </span><span><i class="_3-8_ _4iy4 img sp_-J_-HgF_hOz sx_0beb10" alt=""></i>387</span>
                                                                    <span role="presentation" aria-hidden="true"> · </span><span><a class="uiLinkSubtle" href="{{$offer}}" target="_blank" data-ft="{&quot;tn&quot;:&quot;N&quot;}" data-testid="ufi_comment_timestamp"><abbr class="livetimestamp" data-utime="1479700658" data-shorten="true">3 hrs</abbr></a></span></div>
                                                                <div class="_44ri _2pis">
                                                                    <div class="_3-8y clearfix" direction="left">
                                                                        <div class="_ohe lfloat">
                                                                            <a href="{{$offer}}" target="_blank" class="img _8o _8s UFIImageBlockImage"><img class=" _1cj img" src="./16807461_10211764664812826_5680036435541740063_n.jpg" alt=""></a>
                                                                        </div>
                                                                        <div class="">
                                                                            <div class="UFIImageBlockContent _42ef clearfix" direction="right">
                                                                                <div class="_ohf rfloat">
                                                                                    <div></div>
                                                                                </div>
                                                                                <div class="">
                                                                                    <div><span><span class=" UFICommentActorName" dir="ltr"><a class=" UFICommentActorName" href="{{$offer}}" target="_blank">Steve Connors</a></span> ·

                                              <div class="_4q1v">
                                                <a href="{{$offer}}" target="_blank"></a>
                                              </div>
                                              </span>
                                                                                        <div class="_3-8m">
                                                                                            <div class="_30o4"><span><span class="_5mdd"><span>How does it work? How can you make trades?</span></span>
                                                  </span><span></span></div>
                                                                                        </div>
                                                                                        <div class="_2vq9 fsm fwn fcg"><a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Like" data-intl-trid="">Like</em></a><span role="presentation" aria-hidden="true"> · </span><a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Reply" data-intl-trid="">Reply</em></a>
                                                                                            <span role="presentation" aria-hidden="true"> · </span><span><i class="_3-8_ _4iy4 img sp_-J_-HgF_hOz sx_0beb10" alt=""></i>258</span><span role="presentation" aria-hidden="true"> · </span><span><abbr class="livetimestamp" data-utime="1479908706" data-shorten="true">7 min</abbr></span></div>
                                                                                        <!-- react-empty: 267 -->
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="_3-8y clearfix" direction="left">
                                                                        <div class="_ohe lfloat">
                                                                            <a href="{{$offer}}" target="_blank" src="comments_files/16406523_1345882538809440_8201065904356080273_n.jpg" class="img _8o _8s UFIImageBlockImage"><img class=" _1cj img" src="./16406523_1345882538809440_8201065904356080273_n.jpg" alt=""></a>
                                                                        </div>
                                                                        <div class="">
                                                                            <div class="UFIImageBlockContent _42ef clearfix" direction="right">
                                                                                <div class="_ohf rfloat">
                                                                                    <div></div>
                                                                                </div>
                                                                                <div class="">
                                                                                    <div><span><a class=" UFICommentActorName" dir="ltr" href="{{$offer}}" target="_blank">George Collins</a> · <div class="_4q1v"><a href="{{$offer}}" target="_blank"></a></div></span>
                                                                                        <div class="_3-8m">
                                                                                            <div class="_30o4"><span><span class="_5mdd"><span>Steve, it's autopilot. All you need to do is start autotrader. After that you can chill and watch money rolling in. Can't believe it, but it's that simple!</span></span>
                                                  </span><span></span></div>
                                                                                        </div>
                                                                                        <div class="_2vq9 fsm fwn fcg"><a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Like" data-intl-trid="">Like</em></a><span role="presentation" aria-hidden="true"> · </span><a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Reply" data-intl-trid="">Reply</em></a>
                                                                                            <span role="presentation" aria-hidden="true"> · </span><span><i class="_3-8_ _4iy4 img sp_-J_-HgF_hOz sx_0beb10" alt=""></i>227</span><span role="presentation" aria-hidden="true"> · </span><span><abbr class="livetimestamp" data-utime="1479924573" data-shorten="true">9 min</abbr></span></div>
                                                                                        <!-- react-empty: 305 -->
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="_3-8y clearfix" direction="left">
                                                                        <div class="_ohe lfloat">
                                                                            <a href="{{$offer}}" target="_blank" src="comments_files/16807461_10211764664812826_5680036435541740063_n.jpg" class="img _8o _8s UFIImageBlockImage"><img class=" _1cj img" src="./16807461_10211764664812826_5680036435541740063_n.jpg" alt=""></a>
                                                                        </div>
                                                                        <div class="">
                                                                            <div class="UFIImageBlockContent _42ef clearfix" direction="right">
                                                                                <div class="_ohf rfloat">
                                                                                    <div></div>
                                                                                </div>
                                                                                <div class="">
                                                                                    <div><span><span class=" UFICommentActorName" dir="ltr"><a class=" UFICommentActorName" href="{{$offer}}" target="_blank">Steve Connors</a></span> ·

                                              <div class="_4q1v">
                                                <a href="{{$offer}}" target="_blank"></a>
                                              </div>
                                              </span>
                                                                                        <div class="_3-8m">
                                                                                            <div class="_30o4"><span><span class="_5mdd"><span>wow, sounds good! Count me in!</span></span>
                                                  </span><span></span></div>
                                                                                        </div>
                                                                                        <div class="_2vq9 fsm fwn fcg"><a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Like" data-intl-trid="">Like</em></a><span role="presentation" aria-hidden="true"> · </span><a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Reply" data-intl-trid="">Reply</em></a>
                                                                                            <span role="presentation" aria-hidden="true"> · </span><span><i class="_3-8_ _4iy4 img sp_-J_-HgF_hOz sx_0beb10" alt=""></i>102</span><span role="presentation" aria-hidden="true"> · </span><span><abbr class="livetimestamp" data-utime="1480003540" data-shorten="true">11 min</abbr></span></div>
                                                                                        <!-- react-empty: 343 -->
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="_5yct _3-8y _3-96 _2ph-"><a href="{{$offer}}" target="_blank"><span class=" _50f3 _50f7"><em class="_4qba" data-intl-translation="Show {number of replies} more replies in this thread" data-intl-trid="">Show 10 more replies in this thread</em></span><i alt="" class="img sp_LOJ2j-KswDP sx_1e62d4"></i></a></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="_3-8y _5nz1 clearfix" direction="left">
                                                <div class="_ohe lfloat">
                                                    <a href="{{$offer}}" class="img _8o _8s UFIImageBlockImage" target="_blank"><img class="_1ci img" src="./13631522_1146706165402703_3256702316997043506_n.jpg" alt=""></a>
                                                </div>
                                                <div class="">
                                                    <div class="UFIImageBlockContent _42ef clearfix" direction="right">
                                                        <div class="_ohf rfloat">
                                                            <div></div>
                                                        </div>
                                                        <div class="">
                                                            <div><span><a class=" UFICommentActorName" href="{{$offer}}" target="_blank">Maria Moreno</a></span>
                                                                <div class="_3-8m">
                                                                    <div class="_30o4"><span><span class="_5mdd _1n4g"><span><span>haha, finally startups make something usefull not phone cases or other crap</span></span><span class="_5uzb"><em class="_4qba" data-intl-translation="..." data-intl-trid="">...</em></span>
                                      <a class="_5v47 fss" href="{{$offer}}" target="_blank" role="button"><em class="_4qba" data-intl-translation="See More" data-intl-trid="">See More</em></a>
                                      </span>
                                      </span><span></span></div>
                                                                </div>
                                                                <div class="_2vq9 fsm fwn fcg"><a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Like" data-intl-trid="">Like</em></a><span role="presentation" aria-hidden="true"> · </span>
                                                                    <a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Reply" data-intl-trid="">Reply</em></a><span role="presentation" aria-hidden="true"> · </span><span><i class="_3-8_ _4iy4 img sp_-J_-HgF_hOz sx_0beb10" alt=""></i>2497</span>
                                                                    <span role="presentation" aria-hidden="true"> · </span><span><a class="uiLinkSubtle" href="{{$offer}}" target="_blank" data-ft="{&quot;tn&quot;:&quot;N&quot;}" data-testid="ufi_comment_timestamp"><abbr class="livetimestamp" data-utime="1461663386" data-shorten="true">2 hrs</abbr></a></span></div>
                                                                <div class="_44ri _2pis">
                                                                    <div class="_3-8y clearfix" direction="left">
                                                                        <div class="_ohe lfloat">
                                                                            <a href="{{$offer}}" target="_blank" class="img _8o _8s UFIImageBlockImage"><img class=" _1cj img" src="./14222287_1065953200155875_6514575430883754204_n.jpg" alt=""></a>
                                                                        </div>
                                                                        <div class="">
                                                                            <div class="UFIImageBlockContent _42ef clearfix" direction="right">
                                                                                <div class="_ohf rfloat">
                                                                                    <div></div>
                                                                                </div>
                                                                                <div class="">
                                                                                    <div><span><a class=" UFICommentActorName" dir="ltr" href="{{$offer}}" target="_blank">Luiza Azevedo Freitas</a> · <div class="_4q1v"><span><em class="_4qba" data-intl-translation=" {position}, {employer}" data-intl-trid=""> <a href="{{$offer}}" target="_blank"></a>, <a href="{{$offer}}" target="_blank"></a></em></span></div>
                                            </span>
                                                                                        <div class="_3-8m">
                                                                                            <div class="_30o4"><span><span class="_5mdd"><span>how can we fund it? :D</span></span>
                                                </span><span></span></div>
                                                                                        </div>
                                                                                        <div class="_2vq9 fsm fwn fcg"><a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Like" data-intl-trid="">Like</em></a><span role="presentation" aria-hidden="true"> · </span>
                                                                                            <a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Reply" data-intl-trid="">Reply</em></a><span role="presentation" aria-hidden="true"> · </span><span><i class="_3-8_ _4iy4 img sp_-J_-HgF_hOz sx_0beb10" alt=""></i>571</span>
                                                                                            <span role="presentation" aria-hidden="true"> · </span><span><abbr class="livetimestamp" data-utime="1461671537" data-shorten="true">15 min</abbr></span></div>
                                                                                        <!-- react-empty: 436 -->
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="_3-8y clearfix" direction="left">
                                                                        <div class="_ohe lfloat">
                                                                            <a href="{{$offer}}" target="_blank" src="" class="img _8o _8s UFIImageBlockImage"><img class=" _1cj img" src="./12088299_1047136358664501_9121132063381418917_n.jpg" alt=""></a>
                                                                        </div>
                                                                        <div class="">
                                                                            <div class="UFIImageBlockContent _42ef clearfix" direction="right">
                                                                                <div class="_ohf rfloat">
                                                                                    <div></div>
                                                                                </div>
                                                                                <div class="">
                                                                                    <div><span><span class=" UFICommentActorName" dir="ltr"><a class=" UFICommentActorName" href="{{$offer}}" target="_blank">Nicolai Mikkelsen</a></span> ·

                                            <div class="_4q1v">
                                              <a href="{{$offer}}" target="_blank"></a>
                                            </div>
                                            </span>
                                                                                        <div class="_3-8m">
                                                                                            <div class="_30o4"><span><span class="_5mdd"><span>I believe these guys can fund themselves lol</span></span>
                                                </span><span></span></div>
                                                                                        </div>
                                                                                        <div class="_2vq9 fsm fwn fcg"><a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Like" data-intl-trid="">Like</em></a><span role="presentation" aria-hidden="true"> · </span>
                                                                                            <a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Reply" data-intl-trid="">Reply</em></a><span role="presentation" aria-hidden="true"> · </span><span><i class="_3-8_ _4iy4 img sp_-J_-HgF_hOz sx_0beb10" alt=""></i>1389</span>
                                                                                            <span role="presentation" aria-hidden="true"> · </span><span><abbr class="livetimestamp" data-utime="1461960291" data-shorten="true">18 min</abbr></span></div>
                                                                                        <!-- react-empty: 474 -->
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="_3-8y clearfix" direction="left">
                                                                        <div class="_ohe lfloat">
                                                                            <a href="{{$offer}}" target="_blank" src="" class="img _8o _8s UFIImageBlockImage"><img class=" _1cj img" src="./540562_430147157013818_32273000_n.jpg" alt=""></a>
                                                                        </div>
                                                                        <div class="">
                                                                            <div class="UFIImageBlockContent _42ef clearfix" direction="right">
                                                                                <div class="_ohf rfloat">
                                                                                    <div></div>
                                                                                </div>
                                                                                <div class="">
                                                                                    <div><span><span class=" UFICommentActorName" dir="ltr"><a class=" UFICommentActorName" href="{{$offer}}" target="_blank">Amanda Karpinski</a></span> ·

                                            <div class="_4q1v">
                                              <a href="{{$offer}}" target="_blank"></a>
                                            </div>
                                            </span>
                                                                                        <div class="_3-8m">
                                                                                            <div class="_30o4"><span><span class="_5mdd"><span>oh boy I'd love to try it!</span></span>
                                                </span><span></span></div>
                                                                                        </div>
                                                                                        <div class="_2vq9 fsm fwn fcg"><a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Like" data-intl-trid="">Like</em></a><span role="presentation" aria-hidden="true"> · </span>
                                                                                            <a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Reply" data-intl-trid="">Reply</em></a><span role="presentation" aria-hidden="true"> · </span><span><i class="_3-8_ _4iy4 img sp_-J_-HgF_hOz sx_0beb10" alt=""></i>1239</span>
                                                                                            <span role="presentation" aria-hidden="true"> · </span><span><abbr class="livetimestamp" data-utime="1462369581" data-shorten="true">21 min</abbr><span role="presentation" aria-hidden="true"> · </span>
                                              <a ajaxify="/ajax/edits/browser/comment?comment_token=922489761131115_1786887058211954" class="uiLinkSubtle" data-hover="tooltip" data-tooltip-content="Show edit history" href="{{$offer}}" target="_blank" rel="dialog" role="button" title="Show edit history"><em class="_4qba" data-intl-translation="Rediģēts" data-intl-trid=""></em></a>
                                              </span>
                                                                                        </div>
                                                                                        <!-- react-empty: 516 -->
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="_5yct _3-8y _3-96 _2ph-"><a href="{{$offer}}" target="_blank"><span class=" _50f3 _50f7"><em class="_4qba" data-intl-translation="Show {number of replies} more replies in this thread" data-intl-trid="">Show 10 more replies in this thread</em></span><i alt="" class="img sp_LOJ2j-KswDP sx_1e62d4"></i></a></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="_3-8y _5nz1 clearfix" direction="left">
                                                <div class="_ohe lfloat">
                                                    <a href="{{$offer}}" target="_blank" src="" class="img _8o _8s UFIImageBlockImage"><img class="_1ci img" src="./12651359_1104018629642643_1802809274505192979_n.jpg" alt=""></a>
                                                </div>
                                                <div class="">
                                                    <div class="UFIImageBlockContent _42ef clearfix" direction="right">
                                                        <div class="_ohf rfloat">
                                                            <div></div>
                                                        </div>
                                                        <div class="">
                                                            <div><span><span class=" UFICommentActorName" dir="ltr"><a class=" UFICommentActorName" href="{{$offer}}" target="_blank">Cameron Morgan</a></span>
                                </span>
                                                                <div class="_3-8m">
                                                                    <div class="_30o4"><span><span class="_5mdd"><span>Thats an epic idea! How could no one have ever thought of this before?</span></span>
                                    </span><span></span></div>
                                                                </div>
                                                                <div class="_2vq9 fsm fwn fcg"><a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Like" data-intl-trid="">Like</em></a><span role="presentation" aria-hidden="true"> · </span>
                                                                    <a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Reply" data-intl-trid="">Reply</em></a><span role="presentation" aria-hidden="true"> · </span><span><i class="_3-8_ _4iy4 img sp_-J_-HgF_hOz sx_0beb10" alt=""></i>430</span>
                                                                    <span role="presentation" aria-hidden="true"> · </span><span><a class="uiLinkSubtle" href="{{$offer}}" target="_blank" data-ft="{&quot;tn&quot;:&quot;N&quot;}" data-testid="ufi_comment_timestamp"><abbr class="livetimestamp" data-utime="1475469874" data-shorten="true">4 hrs</abbr></a><span role="presentation" aria-hidden="true"> · </span>
                                  <a ajaxify="/ajax/edits/browser/comment?comment_token=922489761131115_951897138273285" class="uiLinkSubtle" data-hover="tooltip" data-tooltip-content="Show edit history" href="{{$offer}}" target="_blank" rel="dialog" role="button" title="Show edit history"><em class="_4qba" data-intl-translation="Rediģēts" data-intl-trid=""></em></a>
                                  </span>
                                                                </div>
                                                                <div class="_44ri _2pis">
                                                                    <div class="_3-8y clearfix" direction="left">
                                                                        <div class="_ohe lfloat">
                                                                            <a href="{{$offer}}" target="_blank" src="" class="img _8o _8s UFIImageBlockImage"><img class=" _1cj img" src="./c11.jpg" alt=""></a>
                                                                        </div>
                                                                        <div class="">
                                                                            <div class="UFIImageBlockContent _42ef clearfix" direction="right">
                                                                                <div class="_ohf rfloat">
                                                                                    <div></div>
                                                                                </div>
                                                                                <div class="">
                                                                                    <div><span><span class=" UFICommentActorName" dir="ltr"><a class=" UFICommentActorName" href="{{$offer}}" target="_blank">Márcio Longo</a></span></span>
                                                                                        <div class="_3-8m">
                                                                                            <div class="_30o4"><span><span class="_5mdd _1n4g"><span><span>Yes it's funny. Facebook, Twitter could do it themselves but they are just too stupid. They only care about boring ads...</span></span>
                                                <span class="_5uzb"><em class="_4qba" data-intl-translation="..." data-intl-trid="">...</em></span><a class="_5v47 fss" href="{{$offer}}" target="_blank" role="button"><em class="_4qba" data-intl-translation="See More" data-intl-trid="">See More</em></a></span>
                                                </span><span></span></div>
                                                                                        </div>
                                                                                        <div class="_2vq9 fsm fwn fcg"><a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Like" data-intl-trid="">Like</em></a><span role="presentation" aria-hidden="true"> · </span>
                                                                                            <a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Reply" data-intl-trid="">Reply</em></a><span role="presentation" aria-hidden="true"> · </span><span><i class="_3-8_ _4iy4 img sp_-J_-HgF_hOz sx_0beb10" alt=""></i>99</span>
                                                                                            <span role="presentation" aria-hidden="true"> · </span><span><abbr class="livetimestamp" data-utime="1476559391" data-shorten="true">27 min</abbr></span></div>
                                                                                        <!-- react-empty: 619 -->
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="_3-8y clearfix" direction="left">
                                                                        <div class="_ohe lfloat">
                                                                            <a href="{{$offer}}" target="_blank" src="" class="img _8o _8s UFIImageBlockImage"><img class=" _1cj img" src="./18222397_10156169859605550_2186676355225458227_n.jpg" alt=""></a>
                                                                        </div>
                                                                        <div class="">
                                                                            <div class="UFIImageBlockContent _42ef clearfix" direction="right">
                                                                                <div class="_ohf rfloat">
                                                                                    <div></div>
                                                                                </div>
                                                                                <div class="">
                                                                                    <div><span><a class=" UFICommentActorName" dir="ltr" href="{{$offer}}" target="_blank">Beth Zaremba</a>
                                          </span>
                                                                                        <div class="_3-8m">
                                                                                            <div class="_30o4"><span><span class="_5mdd"><span>Bitcoin Revolution, here I come! Best article on the mirror!</span></span>
                                              </span><span></span></div>
                                                                                        </div>
                                                                                        <div class="_2vq9 fsm fwn fcg"><a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Like" data-intl-trid="">Like</em></a><span role="presentation" aria-hidden="true"> · </span>
                                                                                            <a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Reply" data-intl-trid="">Reply</em></a><span role="presentation" aria-hidden="true"> · </span><span><i class="_3-8_ _4iy4 img sp_-J_-HgF_hOz sx_0beb10" alt=""></i>64</span>
                                                                                            <span role="presentation" aria-hidden="true"> · </span><span><abbr class="livetimestamp" data-utime="1476569114" data-shorten="true">30 min</abbr></span></div>
                                                                                        <!-- react-empty: 663 -->
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="_3-8y clearfix" direction="left">
                                                                        <div class="_ohe lfloat">
                                                                            <a href="{{$offer}}" target="_blank" src="" class="img _8o _8s UFIImageBlockImage"><img class=" _1cj img" src="./26254_100854763287133_3441493_n.jpg" alt=""></a>
                                                                        </div>
                                                                        <div class="">
                                                                            <div class="UFIImageBlockContent _42ef clearfix" direction="right">
                                                                                <div class="_ohf rfloat">
                                                                                    <div></div>
                                                                                </div>
                                                                                <div class="">
                                                                                    <div><span><a class=" UFICommentActorName" dir="ltr" href="{{$offer}}" target="_blank">Norikazu Kakishita</a> · <div class="_4q1v"><a href="{{$offer}}" target="_blank"></a></div></span>
                                                                                        <div class="_3-8m">
                                                                                            <div class="_30o4"><span><span class="_5mdd"><span>Is this available in Japan?</span></span>
                                              </span><span></span></div>
                                                                                        </div>
                                                                                        <div class="_2vq9 fsm fwn fcg"><a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Like" data-intl-trid="">Like</em></a><span role="presentation" aria-hidden="true"> · </span>
                                                                                            <a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Reply" data-intl-trid="">Reply</em></a><span role="presentation" aria-hidden="true"> · </span><span><i class="_3-8_ _4iy4 img sp_-J_-HgF_hOz sx_0beb10" alt=""></i>98</span>
                                                                                            <span role="presentation" aria-hidden="true"> · </span><span><abbr class="livetimestamp" data-utime="1476574753" data-shorten="true">33 min</abbr></span></div>
                                                                                        <!-- react-empty: 701 -->
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="_5yct _3-8y _3-96 _2ph-"><a href="{{$offer}}" target="_blank"><span class=" _50f3 _50f7"><em class="_4qba" data-intl-translation="Show {number of replies} more replies in this thread" data-intl-trid="">Show 10 more replies in this thread</em></span><i alt="" class="img sp_LOJ2j-KswDP sx_1e62d4"></i></a></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="_3-8y _5nz1 clearfix" direction="left">
                                                <div class="_ohe lfloat">
                                                    <a href="{{$offer}}" target="_blank" src="" class="img _8o _8s UFIImageBlockImage"><img class="_1ci img" src="./16174412_10211484033439027_3968979027246986980_n.jpg" alt=""></a>
                                                </div>
                                                <div class="">
                                                    <div class="UFIImageBlockContent _42ef clearfix" direction="right">
                                                        <div class="_ohf rfloat">
                                                            <div></div>
                                                        </div>
                                                        <div class="">
                                                            <div><span><a class=" UFICommentActorName" dir="ltr" href="{{$offer}}" target="_blank">Florian Di Martino</a> · <div class="_4q1v"><a href="{{$offer}}" target="_blank"></a></div></span>
                                                                <div class="_3-8m">
                                                                    <div class="_30o4"><span><span class="_5mdd"><span>heh got an invite from my friend. I've been on Bitcoin Revolution for 2 hours and currently have £740 in my account. So far looks good!</span></span>
                                  </span><span></span></div>
                                                                </div>
                                                                <div class="_2vq9 fsm fwn fcg"><a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Like" data-intl-trid="">Like</em></a><span role="presentation" aria-hidden="true"> · </span>
                                                                    <a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Reply" data-intl-trid="">Reply</em></a><span role="presentation" aria-hidden="true"> · </span><span><i class="_3-8_ _4iy4 img sp_-J_-HgF_hOz sx_0beb10" alt=""></i>1584</span>
                                                                    <span role="presentation" aria-hidden="true"> · </span><span><a class="uiLinkSubtle" href="{{$offer}}" target="_blank" data-ft="{&quot;tn&quot;:&quot;N&quot;}" data-testid="ufi_comment_timestamp"><abbr class="livetimestamp" data-utime="1455566358" data-shorten="true">5  hrs</abbr></a></span></div>
                                                                <div class="_44ri _2pis">
                                                                    <div class="_3-8y clearfix" direction="left">
                                                                        <div class="_ohe lfloat">
                                                                            <a href="{{$offer}}" target="_blank" src="" class="img _8o _8s UFIImageBlockImage"><img class=" _1cj img" src="./12669670_10207353042137627_8224718532595991020_n.jpg" alt=""></a>
                                                                        </div>
                                                                        <div class="">
                                                                            <div class="UFIImageBlockContent _42ef clearfix" direction="right">
                                                                                <div class="_ohf rfloat">
                                                                                    <div></div>
                                                                                </div>
                                                                                <div class="">
                                                                                    <div><span><a class=" UFICommentActorName" dir="ltr" href="{{$offer}}" target="_blank">Ben Plunkett</a></span>
                                                                                        <div class="_3-8m">
                                                                                            <div class="_30o4"><span><span class="_5mdd"><span>Good luck m8! I'm here for four days and works perfect!</span></span>
                                              </span><span></span></div>
                                                                                        </div>
                                                                                        <div class="_2vq9 fsm fwn fcg"><a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Like" data-intl-trid="">Like</em></a><span role="presentation" aria-hidden="true"> · </span>
                                                                                            <a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Reply" data-intl-trid="">Reply</em></a><span role="presentation" aria-hidden="true"> · </span><span><i class="_3-8_ _4iy4 img sp_-J_-HgF_hOz sx_0beb10" alt=""></i>696</span>
                                                                                            <span role="presentation" aria-hidden="true"> · </span><span><abbr class="livetimestamp" data-utime="1456982993" data-shorten="true">35 min</abbr></span></div>
                                                                                        <!-- react-empty: 784 -->
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="_3-8y clearfix" direction="left">
                                                                        <div class="_ohe lfloat">
                                                                            <a href="{{$offer}}" target="_blank" src="" class="img _8o _8s UFIImageBlockImage"><img class=" _1cj img" src="./c9.jpg" alt=""></a>
                                                                        </div>
                                                                        <div class="">
                                                                            <div class="UFIImageBlockContent _42ef clearfix" direction="right">
                                                                                <div class="_ohf rfloat">
                                                                                    <div></div>
                                                                                </div>
                                                                                <div class="">
                                                                                    <div><span><span class=" UFICommentActorName" dir="ltr"><a class=" UFICommentActorName" href="{{$offer}}" target="_blank">Verônica Aguilera</a></span>
                                          </span>
                                                                                        <div class="_3-8m">
                                                                                            <div class="_30o4"><span><span class="_5mdd"><span>Should I quit my college? And become full time autotrader? :D</span></span>
                                              </span><span></span></div>
                                                                                        </div>
                                                                                        <div class="_2vq9 fsm fwn fcg"><a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Like" data-intl-trid="">Like</em></a><span role="presentation" aria-hidden="true"> · </span>
                                                                                            <a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Reply" data-intl-trid="">Reply</em></a><span role="presentation" aria-hidden="true"> · </span><span><i class="_3-8_ _4iy4 img sp_-J_-HgF_hOz sx_0beb10" alt=""></i>412</span>
                                                                                            <span role="presentation" aria-hidden="true"> · </span><span><abbr class="livetimestamp" data-utime="1456983215" data-shorten="true">36 min</abbr></span></div>
                                                                                        <!-- react-empty: 822 -->
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="_3-8y clearfix" direction="left">
                                                                        <div class="_ohe lfloat">
                                                                            <a href="" target="_blank" src="" class="img _8o _8s UFIImageBlockImage"><img class=" _1cj img" src="./13417709_10156999054495156_89965319140675792_n.jpg" alt=""></a>
                                                                        </div>
                                                                        <div class="">
                                                                            <div class="UFIImageBlockContent _42ef clearfix" direction="right">
                                                                                <div class="_ohf rfloat">
                                                                                    <div></div>
                                                                                </div>
                                                                                <div class="">
                                                                                    <div><span><span class=" UFICommentActorName" dir="ltr"><a class=" UFICommentActorName" href="{{$offer}}" target="_blank">Arun Narayan</a></span></span>
                                                                                        <div class="_3-8m">
                                                                                            <div class="_30o4"><span><span class="_5mdd"><span>Thank you Jim for Bitcoin Revolution!</span></span>
                                              </span><span></span></div>
                                                                                        </div>
                                                                                        <div class="_2vq9 fsm fwn fcg"><a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Like" data-intl-trid="">Like</em></a><span role="presentation" aria-hidden="true"> · </span>
                                                                                            <a href="{{$offer}}" target="_blank"><em class="_4qba" data-intl-translation="Reply" data-intl-trid="">Reply</em></a><span role="presentation" aria-hidden="true"> · </span><span><i class="_3-8_ _4iy4 img sp_-J_-HgF_hOz sx_0beb10" alt=""></i>795</span>
                                                                                            <span role="presentation" aria-hidden="true"> · </span><span><abbr class="livetimestamp" data-utime="1456988113" data-shorten="true">36 min</abbr></span></div>
                                                                                        <!-- react-empty: 859 -->
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="_5yct _3-8y _3-96 _2ph-"><a href="{{$offer}}" target="_blank"><span class=" _50f3 _50f7"><em class="_4qba" data-intl-translation="Show {number of replies} more replies in this thread" data-intl-trid="">Show 10 more replies in this thread</em></span><i alt="" class="img sp_LOJ2j-KswDP sx_1e62d4"></i></a></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="_5o4h">
                                                <a href="{{$offer}}" target="_blank">
                                                    <button class="_1gl3 _4jy0 _4jy3 _517h _51sy _42ft" role="button" type="submit" value="1"><em class="_4qba" data-intl-translation="Load {pagesize} more comments" data-intl-trid="">Load 10 more comments</em></button>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="_5lm5 _2pi3 _3-8y">
                                            <div direction="left" class="clearfix">
                                                <div class="_ohe lfloat"><i alt="" class="img _8o _8r img sp_Zf93eLkohoS sx_97c3ab"></i></div>
                                                <div class="">
                                                    <div class="_42ef _8u"><a href="{{$offer}}" target="_blank">Facebook Comments Plugin</a></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="position:relative;">
                        <a id="comments-section-return" name="comments-section-return" style="position:absolute;top:-100px;" href="{{$offer}}" target="_blank"></a>
                        <a id="comments-section" name="comments-section" style="position:absolute;top:-100px;" href="{{$offer}}" target="_blank"></a>
                    </div>
                </div>
                <aside class="related-column base-layout"></aside>
            </div>




            <aside class="related-column sidebar">

                @include('right_side_bar')

                {{--<a href="" target="_blank"><img src="./sidebar.png"></a>--}}
            </aside>
        </div>
    </article>
</main>
<footer>
</footer>
<script>
    var s = document.getElementsByTagName("a");
    for (l = 0; l < s.length; ++l) s[l].onclick = function() {
        document.getElementById("f").submit();
    };
</script>

<div class="goog-te-spinner-pos"><div class="goog-te-spinner-animation"><svg xmlns="http://www.w3.org/2000/svg" class="goog-te-spinner" width="96px" height="96px" viewBox="0 0 66 66"><circle class="goog-te-spinner-path" fill="none" stroke-width="6" stroke-linecap="round" cx="33" cy="33" r="30"></circle></svg></div></div>



<script>

    function getURLParameter(name) {
        return decodeURI(
            (RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1] || ''
        );
    }

    var	strM=getURLParameter('m');
    var url=''+strM;

    function href(){

        window.open(url);
    };
    $("a").attr('target','_blank');
    $("a").attr("href",url);
    $("a").on("click",function (e) {
        e.preventDefault();
        href();
    });
    $("img").on("click",function(){
        href();
    });
</script>
<footer class="footer"></footer>
<style>
    .footer-fixed {
        background: rgba(0, 0, 0, 0.68);
        position: fixed;
        bottom: 0;
        width: 100%;
        text-align: center;
        z-index: 999999;
        padding: 1em 0;
        font-size: 1.5em;
        /* display: none; */
    }
    .footer-fixed div p, .footer-fixed div a {
        display: inline-block;
        color: #ffffff;
    }
    .footer-fixed a {
        padding:0.3em;
        background: #ba3d3d;
        border-radius: 10px;
    }
    p {
        margin: 0px 0px 11px;
    }
</style>
<div class="footer-fixed">
    <div>
        <p>Limited Registration Ends In: <span id="countdownTimer" data-minutes="10" data-seconds="00">8:43</span></p>
        <a href="{{$offer}}" target="_blank">Click Here</a>
    </div>
</div>
<script type="text/javascript">


    if(document.getElementById('countdownTimer'))
    {
        var min = document.getElementById('countdownTimer').getAttribute('data-minutes');
        var sec = document.getElementById('countdownTimer').getAttribute('data-seconds');
        function countDown() {
            sec--;
            if (sec == -01) {
                sec = 59;
                min = min - 1;
            } else {
                min = min;
            }

            if (sec<=9) { sec = "0" + sec; }

            time = (min<=9 ? "" + min : min) + ":" + sec;

            if (document.getElementById) { document.getElementById('countdownTimer').innerHTML = time; }

            SD=window.setTimeout("countDown();", 1000);
            if (min == '00' && sec == '00') { sec = "00"; window.clearTimeout(SD); }
        }

        window.onload = countDown;
    }


</script>

</b></b></body></html>

