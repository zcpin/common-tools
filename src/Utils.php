<?php

declare(strict_types=1);

namespace CommonTools;

class Utils
{
    /**
     * 下划线转驼峰
     * @param string $str
     * @return string
     */
    public static function lineToHump(string $str): string
    {
        return preg_replace_callback('/([-_]+([a-z]))/i', static function ($matches) {
            return strtoupper($matches[2]);
        }, $str);
    }

    /**
     * 驼峰转下划线
     * @param string $str
     * @return string
     */
    public static function humpToLine(string $str): string
    {
        return preg_replace_callback('/([A-Z])/', static function ($matches) {
            return '_' . strtolower($matches[0]);
        }, $str);
    }

    /**
     * 获取真实IP
     * @return mixed
     */
    public static function getRealIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
            foreach ($matches[0] as $xip) {
                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;
                    break;
                }
            }
        } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^(\d{1,3}\.){3}\d{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && preg_match('/^(\d{1,3}\.){3}\d{1,3}$/', $_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (isset($_SERVER['HTTP_X_REAL_IP']) && preg_match('/^(\d{1,3}\.){3}\d{1,3}$/', $_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        }
        return $ip;
    }

    /**
     * 读取文件夹下的所有文件
     * @param string $path
     * @param string $basePath
     * @return array
     */
    public static function readDirAllFiles(string $path, string $basePath = ''): array
    {
        [$list, $temp_list] = [[], scandir($path)];
        empty($basePath) && $basePath = $path;
        foreach ($temp_list as $file) {
            if ($file != ".." && $file != ".") {
                if (is_dir($path . DIRECTORY_SEPARATOR . $file)) {
                    $childFiles = self::readDirAllFiles($path . DIRECTORY_SEPARATOR . $file, $basePath);
                    $list       += $childFiles;
                } else {
                    $filePath        = $path . DIRECTORY_SEPARATOR . $file;
                    $fileName        = str_replace($basePath . DIRECTORY_SEPARATOR, '', $filePath);
                    $list[$fileName] = $filePath;
                }
            }
        }
        return $list;
    }

    /**
     * 生成uuid
     */
    public static function uuid(): string
    {
        $chars = md5(uniqid((string)mt_rand(), true));
        return substr($chars, 0, 8) . '-'
            . substr($chars, 8, 4) . '-'
            . substr($chars, 12, 4) . '-'
            . substr($chars, 16, 4) . '-'
            . substr($chars, 20, 12);
    }

    /**
     * 生成随机字符串
     * @param int $length 随机字符串长度
     * @param int $mode 1-特殊字符、数字、大小写字母组合  2-数字、大小写字母  3-大小写字母  4-纯数字
     * @return string
     */
    public static function randomString(int $length = 6, int $mode = 2): string
    {
        $output = '';
        switch ($mode) {
            case 1:
                //包含特殊字符、数字、大写字母、小写字母
                $start = 33;
                $end   = 126;
                break;
            case 2:
                //包含数字、大小写字母
                $start = 48;
                $end   = 122;
                break;
            case 3:
                //纯字母 a~z(97~122) A~Z(65~90)
                $start = 65;
                $end   = 122;
                break;
            case 4:
                //纯数字 0~9
                $start = 48;
                $end   = 57;
                break;
            default:
                $start = 48;
                $end   = 57;
        }
        for ($i = 0; $i < $length; $i++) {
            $ascii = random_int($start, $end);
            if ($mode === 2 || $mode === 3) {
                while (true) {
                    if (($ascii > 57 && $ascii < 65) || ($ascii > 90 && $ascii < 97)) {
                        $ascii = random_int($start, $end);
                    } else {
                        break;
                    }
                }
            }
            $output .= chr($ascii);
        }
        return $output;
    }

    /**
     * 把数组转成树状结构
     * @param array $list
     * @param string $pk
     * @param string $pid
     * @param string $child
     * @param string $root
     * @return array
     */
    public static function arrayToTree(array $list, string $pk = 'id', string $pid = 'pid', string $child = 'children', string $root = '0'): array
    {
        $tree = [];
        if (is_array($list)) {
            $refer = [];
            foreach ($list as $data) {
                $refer[$data[$pk]] = &$data;
            }
            foreach ($list as $key => $data) {
                $parentId = $data[$pid];
                if ($root == $parentId) {
                    $tree[] = &$data;
                } else if (isset($refer[$parentId])) {
                    $parent           = &$refer[$parentId];
                    $parent[$child][] = &$list[$key];
                }
            }
        }

        return $tree;
    }
}
