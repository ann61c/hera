<?php

function hera_get_background_image($post_id, $width = null, $height = null)
{
    global $heraSetting;
    if (has_post_thumbnail($post_id)) {
        $timthumb_src = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full');
        $output = $timthumb_src[0];
    } elseif (get_post_meta($post_id, '_banner', true)) {
        $output = get_post_meta($post_id, '_banner', true);
    } else {
        $content = get_post_field('post_content', $post_id);
        $defaltthubmnail = $heraSetting->get_setting('default_thumbnail');
        preg_match_all('/<img.*?(?: |\\t|\\r|\\n)?src=[\'"]?(.+?)[\'"]?(?:(?: |\\t|\\r|\\n)+.*?)?>/sim', $content, $strResult, PREG_PATTERN_ORDER);
        $n = count($strResult[1]);
        if ($n > 0) {
            $original_image = $strResult[1][0];
            $path_parts = pathinfo($original_image);
            // 确保图片地址有扩展名，再继续处理
            if (isset($path_parts['extension'])) {
                $thumbnail_url = $path_parts['dirname'] . '/' . $path_parts['filename'] . '-150x150.' . $path_parts['extension'];
                
                // 检查 150x150 缩略图是否存在
                $headers = @get_headers($thumbnail_url);
                if ($headers && strpos($headers[0], '200')) {
                    $output = $thumbnail_url; // 存在，使用缩略图
                } else {
                    $output = $original_image; // 不存在，回退到原图
                }
            } else {
                 // 如果图片地址没有扩展名（例如某些API返回的图片），直接使用原地址
                 $output = $original_image;
            }
        } else {
            $output = $defaltthubmnail;
        }
    }

    if ($height && $width) {
        if ($heraSetting->get_setting('upyun')) {
            $output = $output . "!/both/{$width}x{$height}";
        }

        if ($heraSetting->get_setting('oss')) {
            $heraSetting = $output . "?x-oss-process=image/crop,w_{$width},h_{$height}";
        }

        if ($heraSetting->get_setting('qiniu')) {
            $output = $output . "?imageView2/1/w/{$width}/h/{$height}";
        }
    }
    return $output;
}

function hera_is_has_image($post_id)
{
    static $has_image;
    if (has_post_thumbnail($post_id)) {
        $has_image = true;
    } elseif (get_post_meta($post_id, '_banner', true)) {
        $has_image = true;
    } else {
        $content = get_post_field('post_content', $post_id);
        preg_match_all('/<img.*?(?: |\\t|\\r|\\n)?src=[\'"]?(.+?)[\'"]?(?:(?: |\\t|\\r|\\n)+.*?)?>/sim', $content, $strResult, PREG_PATTERN_ORDER);
        $n = count($strResult[1]);
        if ($n > 0) {
            $has_image = true;
        } else {
            $has_image = false;
        }
    }

    return $has_image;
}

/**
 * Get post image count
 *
 * @since Hera 0.0.9
 *
 */


function hera_get_post_image_count($post_id)
{
    $content = get_post_field('post_content', $post_id);
    $content = apply_filters('the_content', $content);
    preg_match_all('/<img.*?(?: |\\t|\\r|\\n)?src=[\'"]?(.+?)[\'"]?(?:(?: |\\t|\\r|\\n)+.*?)?>/sim', $content, $strResult, PREG_PATTERN_ORDER);
    return count($strResult[1]);
}

function hera_get_post_image_count_text($post_id, $before = '', $after = '')
{
    $count = hera_get_post_image_count($post_id);

    if ($count == 0) {
        return '';
    }

    return $before . sprintf(_n('%d pic', '%d pics', $count, 'Hera'), $count) . $after;
}

/**
 * Get post images
 *
 * @since Hera 0.2.0
 *
 */


function hera_get_post_images($post_id, $count = 3)
{
    if (! $post_id) {
        $post_id = get_the_ID();
    }

    $post = get_post($post_id);
    $content = apply_filters('the_content', $post->post_content);
    preg_match_all('/<img.*?(?: |\\t|\\r|\\n)?src=[\'"]?(.+?)[\'"]?(?:(?: |\\t|\\r|\\n)+.*?)?>/sim', $content, $strResult, PREG_PATTERN_ORDER);
    $n = count($strResult[1]);
    $output = array();
    if ($n > 0) {
        $output = array_slice($strResult[1], 0, $count);
    }
    return $output;
}
