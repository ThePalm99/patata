<?php

require_once 'conf.php';


const TYPE_EMOJI = [
    'E' => '💡',
    'I' => '💻',
    'M' => '🔨',
    'D' => '📘',
    'C' => '🧽',
    'V' => '🍀',
    'R' => '💾',
    'S' => '🎮',
];
const TYPE_DESCRIPTION = [
    'E' => 'Elettronica',
    'I' => 'Informatica',
    'M' => 'Meccanica',
    'D' => 'Documentazione',
    'C' => 'Riordino',
    'V' => 'Eventi',
    'R' => 'Retrocomputing',
    'S' => 'Svago',
];
const TASKS_CACHE = 'stacks_cache.json';
const TASKS_CACHE_LIFETIME = 3600 * 1;
const INSTAGRAM_CACHE = 'cache_instagram.json';
const INSTAGRAM_CACHE_LIFETIME = 3600 * 24;
const FACEBOOK_CACHE = 'cache_facebook.json';
const FACEBOOK_CACHE_LIFETIME = INSTAGRAM_CACHE_LIFETIME;


function deck_request(string $url): string {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_USERPWD, DECK_USER . ":" . DECK_PASS);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'OCS-APIRequest: true',
        //'Content-Type: application/json',
    ]);

    $response = curl_exec($ch);

    if(curl_errno($ch)) {
        //If an error occured, throw an Exception.
        throw new RuntimeException(curl_error($ch));
    }

    return $response;
}


function print_stats(string $stats)
{
    require_once 'conf.php';

    $curl = get_curl();

    echo '<div class="row" id="stats" style="overflow: hidden; height: 100%;">';
    print_stat($curl, 'tasks');
    echo '</div>';

    curl_close($curl);
}


function print_social_stats(){
    require_once 'conf.php';

    $socials = ['youtube',
        'facebook',
        'instagram'];

    foreach ($socials as $social){
        echo print_social_stat($social);
    }

}


function print_social_stat($case)
{
    switch ($case) {
        case 'youtube':
            $urls = ['subs' => 'https://www.googleapis.com/youtube/v3/channels?part=statistics&id=' . YOUTUBE_CHANNEL_ID .
                '&fields=items/statistics/subscriberCount&key=' . YOUTUBE_API_KEY ,
                'views' => 'https://www.googleapis.com/youtube/v3/channels?part=statistics&id=' . YOUTUBE_CHANNEL_ID .
                '&fields=items/statistics/viewCount&key=' . YOUTUBE_API_KEY];
            $result = '<div class="col-12 align-middle" style="display: inline;">'.
                '<i class="fa fa-youtube-play" style="vertical-align: middle; font-size: 1.5rem; color: red;"></i>';
            foreach ($urls as $key => $url){
                $query = file_get_contents($url);
                $query = json_decode($query, true);
                if ($key == 'subs'){
                    $query = $query['items'][0]['statistics']['subscriberCount'];
                    $result .= '<i class="pl-1 fa fa-user" style="vertical-align: middle; font-size: 1rem; color: grey;"></i>'.
                        '<span class="pl-1" style="font-size: 1rem; vertical-align: middle;">' .
                        $query . '</span>';
                } else {
                    $query = $query['items'][0]['statistics']['viewCount'];
                    $result .= '<i class="pl-1 fa fa-eye" style="vertical-align: middle; font-size: 1rem; color: grey;"></i>'.
                        '<span class="pl-1" style="font-size: 1rem; vertical-align: middle;">' .
                        $query . '</span>';
                }

            }
            $result .= '</div>';
            break;
        case 'facebook':
            $url = 'https://graph.facebook.com/' . FACEBOOK_PAGE_ID . '/?fields=fan_count&access_token=' . FACEBOOK_ACCESS_TOKEN;
            if (file_exists(FACEBOOK_CACHE)){
                $result = json_decode(file_get_contents(FACEBOOK_CACHE), true);
                if(time() - filemtime(FACEBOOK_CACHE) <= FACEBOOK_CACHE_LIFETIME) {
                    $result = write_cache($url, FACEBOOK_CACHE);
                }
            } else {
                $result = write_cache($url, FACEBOOK_CACHE);
            }
            $result = '<div class="col-12 align-middle" style="display: inline;">'.
                '<i class="fa fa-facebook-square" style="vertical-align: middle; font-size: 1.5rem; color: #3b5998;"></i>'.
                '<span class="pl-1" style="font-size: 1rem; vertical-align: middle;">' .
                $result['fan_count'] . '</span></div>';
            break;
        case 'instagram':
            $url = 'https://www.instagram.com/'. INSTAGRAM_PAGE_ID . '/?__a=1';
            if (file_exists(INSTAGRAM_CACHE)) {
                $result = json_decode(file_get_contents(INSTAGRAM_CACHE), true);
                if (time() - filemtime(INSTAGRAM_CACHE) >= INSTAGRAM_CACHE_LIFETIME) {
                    $result = write_cache($url, INSTAGRAM_CACHE);
                }
            } else {
                $result = write_cache($url, INSTAGRAM_CACHE);
            }
            $result = '<div class="col-12 align-middle" style="display: inline;">'.
                '<i class="fa fa-instagram instagram_icon" style="vertical-align: middle; font-size: 1.5rem; "></i>'.
                '<span class="pl-1" style="font-size: 1rem; vertical-align: middle;">'.
                $result['graphql']['user']['edge_followed_by']['count'] . '</span></div>';

            break;
        default:
            $result = 'BIG ERROR ASD';
    }
    return $result;
}


function write_cache($url, $cache_file){
    $result = file_get_contents($url);
    $result = json_decode($result, true);
    if ($result != null){
        file_put_contents($cache_file, json_encode($result));
    } else if (file_exists($cache_file)){
        $result = json_decode(file_get_contents($cache_file), true);
    }
    return $result;
}


function relative_date($time)
{
    $day = date('Y-m-d', $time);
    if($day === date('Y-m-d', strtotime('today'))) {
        return 'Today';
    } elseif($day === date('Y-m-d', strtotime('yesterday'))) {
        return 'Yesterday';
    } else {
        return $day;
    }
}


function e($text)
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5);
}


/**
 * @param resource $curl CURL
 * @param string $path /v2/whatever
 *
 * @return array
 */
function get_data_from_tarallo($curl, string $path)
{
    $url = TARALLO_URL . $path;
    curl_setopt($curl, CURLOPT_URL, $url);
    $result = curl_exec($curl);
    $result = json_decode($result, true);
    return $result;
}


/**
 * @return false|resource
 */
function get_curl()
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Token ' . TARALLO_TOKEN, 'Accept: application/json']);
    //curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Token ' . TARALLO_TOKEN, 'Accept: application/json', 'Cookie: XDEBUG_SESSION=PHPSTORM']);

    return $curl;
}


function download_tasks(): array
{
    // TODO: use etag/If-Modified-Since instead
    if(file_exists(TASKS_CACHE)) {
        if(time() - filemtime(TASKS_CACHE) <= TASKS_CACHE_LIFETIME) { // 1 hour
            return json_decode(file_get_contents(TASKS_CACHE), true);
        }
    }

    $request = deck_request(DECK_URL . "/apps/deck/api/v1.0/boards/" . DECK_BOARD . "/stacks"); //. "/stacks/" . $stack);
    $stacks_json = json_decode($request, true);
    $stacks_to_display = [];

    // Only show first and second stacks
    foreach ($stacks_json as $key => $value)
        if ($key < 2) $stacks_to_display[] = $value;
        else break;

    unset($stacks_json);

    file_put_contents(TASKS_CACHE, json_encode($stacks_to_display));

    return $stacks_to_display;
}


function get_brightness($hex) {
    // returns brightness value from 0 to 255
    // strip off any leading #
    $c_r = hexdec(substr($hex, 0, 2));
    $c_g = hexdec(substr($hex, 2, 2));
    $c_b = hexdec(substr($hex, 4, 2));

    return (($c_r * 587) + ($c_g * 299) + ($c_b * 114)) / 1000;
}


function print_tasktable()
{
    $stacks = download_tasks();
    $tasks = [];

    foreach($stacks as $stack) {
        // skip if empty stack
        if (!isset($stack['cards'])){
            continue;
        }
        $cards = $stack['cards'];
        $timezone = new DateTimeZone('Europe/Rome');
        foreach ($cards as $card){
            $task = [];
            $labels = $card['labels'];
            $assigned_users = $card['assignedUsers'];
            $task['title'] = $card['title'];
            $task['description'] = $card['description'];
            $task['duedate'] = $card["duedate"] === null?null:new DateTime($card["duedate"], $timezone);
            $task['createdate'] = $card["createdAt"];
            $task['labels'] = [];
            $task['assignee'] = [];
            foreach ($labels as $label){
                $task['labels'][] = [
                        "title" => $label['title'],
                        "color" => $label['color']
                ];
            }
            foreach ($assigned_users as $assignee){
                $displayname = $assignee['participant']['displayname'];
                $displayname = explode(' (', $displayname);
                array_pop($displayname);
                $displayname = implode(" (", $displayname);
                $task['assignee'][] = $displayname;
            }
            $tasks[] = $task;
        }
    }

    // TODO: update everything
    $_SESSION['max_row'] = count($tasks);
    $per_page = 10;
    if (!isset($_SESSION['offset'])) {
        $_SESSION['offset'] = 0;
    }

    $page = 1 + floor(($_SESSION['offset']) / $per_page);
    $pages = ceil($_SESSION['max_row'] / $per_page);
    ?>
    <div id='tasktable'>
<!--        <h5 class='text-center'>Tasklist --><?//= "page $page of $pages" ?><!--</h5>-->
        <table id="tasktable_table" class='table table-striped' style='margin: 0 auto;'>
            <tbody>
            <?php
            foreach ($tasks as $task) {
                // Add title with description and tags
                echo '<tr>';
                echo '<td style="vertical-align: middle;">';
                echo ucfirst(htmlspecialchars($task['title']) . '<br>');
                if ($task['duedate'] != null) {
                    echo '<span class="duedate">Due by ' . htmlspecialchars($task['duedate']->format('d-m-Y')) . '</span> - ';
                }
                if ($task['description'] != null) {
                    echo '<small class="text-muted">' . ucfirst(htmlspecialchars($task['description'])) . '</small><br>';
                }
                echo '<div class="labels-container">';
                foreach ($task['labels'] as $label){
                    echo '<span class="label" style="';
                    echo "background: #{$label['color']};";
                    if (get_brightness($label['color']) < 149){
                        echo "color: white;";
                    } else {
                        echo "color: black;";
                    }
                    echo '">';
                    echo htmlspecialchars($label['title']);
                    echo "</span>";
                }
                echo '</td>';
                echo '<td class="text-center assignee">';
                foreach ($task['assignee'] as $assignee) {
                    echo '<span>' . $assignee;
                    echo '</span><br>';
                }
                echo '</td>';
                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
    <?php
}


function print_stat($curl, string $stat)
{
    switch ($stat) {
        case 'tasks':
            $title = 'Task count';
            $query = json_decode(file_get_contents(TASKS_CACHE), true);
            $data = [];
            foreach ($query as $stack){
                // skip empty stacks
                if (!isset($stack['cards'])){
                    continue;
                }
                $cards = $stack['cards'];
                foreach ($cards as $card){
                    $labels = $card['labels'];
                    foreach ($labels as $label){
                        if (isset($data[$label['title']])){
                            $data[$label['title']] += 1;
                        } else {
                            $data[$label['title']] = 1;
                        }
                    }
                }
            }
            break;
        case 'ram':
            $url = '/v2/stats/getCountByFeature/ram-type/working=yes/rambox';
            $title = 'RAMs available';
            break;
        case 'latest':
            $urls = ['/v2/stats/getRecentAuditByType/U/5',
                '/v2/stats/getRecentAuditByType/C/5'];
            $title = 'Recent items';
            break;
        default:
            echo 'print_stat error: no such case.';
    }

    ?>
    <div class='col-md-6' style="overflow: hidden; height: 100%;">
        <div id="<?= $stat ?>_header">
            <?php if ($stat != 'tasks'): ?>
            <h6 class='text-center'><?= e($title) ?></h6>
            <?php endif; ?>
            <table class='m-0 table table-striped table-sm text-center'>
                <thead style="position: sticky; top: 0;">
                <tr>
                    <?php if ($stat == 'tasks'): ?>
                        <th id="<?= $stat ?>_first_head">Task tag</th>
                    <?php else: ?>
                        <th id="<?= $stat ?>_first_head">Type</th>
                    <?php endif; ?>
                    <th id="<?= $stat ?>_second_head">Qty</th>
                </tr>
                </thead>
            </table>
        </div>
        <div id="<?= $stat ?>_table" style="overflow: hidden;  height: 100%;">
            <table class='m-0 table table-striped table-sm text-center' id="<?= $stat ?>">
                <tbody>
                <?php foreach ($data as $key => $entry): ?>
                    <tr>
                        <td><?= e($key) ?></td>
                        <td><?= e($entry) ?></td>
                    </tr>
                <?php endforeach;?>
                </tbody>
            </table>
        </div>
    </div>

    <!--suppress InfiniteLoopJS -->
    <script type='text/javascript'>

        (async function autoscroll() {
            let table = document.getElementById('<?= $stat ?>_table');
            let firstHeader = document.getElementById('<?= $stat ?>_first_head')
            let secondHeader = document.getElementById('<?= $stat ?>_second_head')

            // // Set correct table header width
            firstHeader.style.width = table.rows[0].cells[0].offsetWidth + "px";
            assigneeHeader.style.width = table.rows[0].cells[1].offsetWidth + "px";
            //Define tasktable autoscroll function

            let $tablediv = $('#<?= $stat ?>_table');

            let velocity = 20;
            let tableHeight = table.clientHeight;
            let duration = tableHeight * velocity;
            while(true) {
                await $tablediv.animate({scrollTop: 0}, 800).promise();
                await $tablediv.animate({scrollTop: 0}, 2000).promise();
                await $tablediv.animate({scrollTop: tableHeight}, duration, "linear").promise();
                console.log(tableHeight);
            }
        })();

        </script>

    <?php
}