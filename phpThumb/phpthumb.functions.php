<?php
/* @codingStandardsIgnoreFile */
//////////////////////////////////////////////////////////////
///  phpThumb() by James Heinrich <info@silisoftware.com>   //
//        available at http://phpthumb.sourceforge.net     ///
//////////////////////////////////////////////////////////////
///                                                         //
// phpthumb.functions.php - general support functions       //
//                                                         ///
//////////////////////////////////////////////////////////////

class phpthumb_functions {

	static function phpinfo_array() {
		static $phpinfo_array = array();
		if (empty($phpinfo_array)) {
			ob_start();
			phpinfo();
			$phpinfo = ob_get_clean();
            $phpinfo_array = explode("\n", $phpinfo);
		}
		return $phpinfo_array;
	}


	static function exif_info() {
		static $exif_info = array();
		if (empty($exif_info)) {
			// based on code by johnschaefer at gmx dot de
			// from PHP help on gd_info()
			$exif_info = array(
				'EXIF Support'           => '',
				'EXIF Version'           => '',
				'Supported EXIF Version' => '',
				'Supported filetypes'    => ''
			);
			$phpinfo_array = self::phpinfo_array();
			foreach ($phpinfo_array as $line) {
				$line = trim(strip_tags($line));
				foreach ($exif_info as $key => $value) {
					if (strpos($line, $key) === 0) {
						$newvalue = trim(str_replace($key, '', $line));
						$exif_info[$key] = $newvalue;
					}
				}
			}
		}
		return $exif_info;
	}


	static function ImageTypeToMIMEtype($imagetype) {
		if (function_exists('image_type_to_mime_type') && $imagetype >= 1 && $imagetype <= 16) {
			// PHP v4.3.0+
			return image_type_to_mime_type($imagetype);
		}
		static $image_type_to_mime_type = array(
			1  => 'image/gif',                     // IMAGETYPE_GIF
			2  => 'image/jpeg',                    // IMAGETYPE_JPEG
			3  => 'image/png',                     // IMAGETYPE_PNG
			4  => 'application/x-shockwave-flash', // IMAGETYPE_SWF
			5  => 'image/psd',                     // IMAGETYPE_PSD
			6  => 'image/bmp',                     // IMAGETYPE_BMP
			7  => 'image/tiff',                    // IMAGETYPE_TIFF_II (intel byte order)
			8  => 'image/tiff',                    // IMAGETYPE_TIFF_MM (motorola byte order)
			9  => 'application/octet-stream',      // IMAGETYPE_JPC
			10 => 'image/jp2',                     // IMAGETYPE_JP2
			11 => 'application/octet-stream',      // IMAGETYPE_JPX
			12 => 'application/octet-stream',      // IMAGETYPE_JB2
			13 => 'application/x-shockwave-flash', // IMAGETYPE_SWC
			14 => 'image/iff',                     // IMAGETYPE_IFF
			15 => 'image/vnd.wap.wbmp',            // IMAGETYPE_WBMP
			16 => 'image/xbm',                     // IMAGETYPE_XBM

			'gif'  => 'image/gif',                 // IMAGETYPE_GIF
			'jpg'  => 'image/jpeg',                // IMAGETYPE_JPEG
			'jpeg' => 'image/jpeg',                // IMAGETYPE_JPEG
			'png'  => 'image/png',                 // IMAGETYPE_PNG
			'bmp'  => 'image/bmp',                 // IMAGETYPE_BMP
			'ico'  => 'image/x-icon',
		);

		return isset($image_type_to_mime_type[$imagetype]) ? $image_type_to_mime_type[$imagetype] : false;
	}


	static function TranslateWHbyAngle($width, $height, $angle) {
		if ($angle % 180 == 0) {
			return [$width, $height];
		}
		$newwidth  = abs(sin(deg2rad($angle))) * $height + abs(cos(deg2rad($angle))) * $width;
		$newheight = abs(sin(deg2rad($angle))) * $width + abs(cos(deg2rad($angle))) * $height;
		return [$newwidth, $newheight];
	}

	static function HexCharDisplay($string) {
		$len = strlen($string);
		$output = '';
		for ($i = 0; $i < $len; $i++) {
			$output .= ' 0x'.str_pad(dechex(ord($string{$i})), 2, '0', STR_PAD_LEFT);
		}
		return $output;
	}


	static function IsHexColor($HexColorString) {
		return preg_match('#^[0-9A-F]{6}$#i', $HexColorString);
	}

	static function ImageColorAllocateAlphaSafe(&$gdimg_hexcolorallocate, $R, $G, $B, $alpha=false) {
		if (PHP_VERSION_ID >= 40302 && $alpha !== false) {
			return ImageColorAllocateAlpha($gdimg_hexcolorallocate, $R, $G, $B, (int)$alpha);
		}
        return ImageColorAllocate($gdimg_hexcolorallocate, $R, $G, $B);
    }

	static function ImageHexColorAllocate(&$gdimg_hexcolorallocate, $HexColorString, $dieOnInvalid=false, $alpha=false) {
		if (!is_resource($gdimg_hexcolorallocate)) {
			die('$gdimg_hexcolorallocate is not a GD resource in ImageHexColorAllocate()');
		}
		if (self::IsHexColor($HexColorString)) {
			$R = hexdec(substr($HexColorString, 0, 2));
			$G = hexdec(substr($HexColorString, 2, 2));
			$B = hexdec(substr($HexColorString, 4, 2));
			return self::ImageColorAllocateAlphaSafe($gdimg_hexcolorallocate, $R, $G, $B, $alpha);
		}
		if ($dieOnInvalid) {
			die('Invalid hex color string: "'.$HexColorString.'"');
		}
		return ImageColorAllocate($gdimg_hexcolorallocate, 0x00, 0x00, 0x00);
	}

	static function HexColorXOR($hexcolor) {
		return strtoupper(str_pad(dechex(~hexdec($hexcolor) & 0xFFFFFF), 6, '0', STR_PAD_LEFT));
	}

	static function GetPixelColor(&$img, $x, $y) {
		if (!is_resource($img)) {
			return false;
		}
		return @ImageColorsForIndex($img, @ImageColorAt($img, $x, $y));
	}


	static function PixelColorDifferencePercent($currentPixel, $targetPixel) {
		$diff = 0;
		foreach ($targetPixel as $channel => $currentvalue) {
			$diff = max($diff, (max($currentPixel[$channel], $targetPixel[$channel]) - min($currentPixel[$channel], $targetPixel[$channel])) / 255);
		}
		return $diff * 100;
	}

	static function GrayscaleValue($r, $g, $b) {
		return round($r * 0.30 + $g * 0.59 + $b * 0.11);
	}

	static function GrayscalePixel($OriginalPixel) {
		$gray = self::GrayscaleValue($OriginalPixel['red'], $OriginalPixel['green'], $OriginalPixel['blue']);
		return array('red'=>$gray, 'green'=>$gray, 'blue'=>$gray);
	}

	static function GrayscalePixelRGB($rgb) {
		$r = $rgb >> 16 & 0xFF;
		$g = $rgb >>  8 & 0xFF;
		$b =  $rgb        & 0xFF;
		return $r * 0.299 + $g * 0.587 + $b * 0.114;
	}

	static function ScaleToFitInBox($width, $height, $maxwidth=null, $maxheight=null, $allow_enlarge=true, $allow_reduce=true) {
		$maxwidth  = $maxwidth === null ? $width  : $maxwidth;
		$maxheight = $maxheight === null ? $height : $maxheight;
		$scale_x = 1;
		$scale_y = 1;
		if ($width > $maxwidth || $width < $maxwidth) {
			$scale_x = $maxwidth / $width;
		}
		if ($height > $maxheight || $height < $maxheight) {
			$scale_y = $maxheight / $height;
		}
		$scale = min($scale_x, $scale_y);
		if (!$allow_enlarge) {
			$scale = min($scale, 1);
		}
		if (!$allow_reduce) {
			$scale = max($scale, 1);
		}
		return $scale;
	}

	static function ImageCopyResampleBicubic($dst_img, $src_img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) {
		// ron at korving dot demon dot nl
		// http://www.php.net/imagecopyresampled

		$scaleX = ($src_w - 1) / $dst_w;
		$scaleY = ($src_h - 1) / $dst_h;

		$scaleX2 = $scaleX / 2.0;
		$scaleY2 = $scaleY / 2.0;

		$isTrueColor = ImageIsTrueColor($src_img);

		for ($y = $src_y; $y < $src_y + $dst_h; $y++) {
			$sY   = $y * $scaleY;
			$siY  = (int) $sY;
			$siY2 = (int) $sY + $scaleY2;

			for ($x = $src_x; $x < $src_x + $dst_w; $x++) {
				$sX   = $x * $scaleX;
				$siX  = (int) $sX;
				$siX2 = (int) $sX + $scaleX2;

				if ($isTrueColor) {

					$c1 = ImageColorAt($src_img, $siX, $siY2);
					$c2 = ImageColorAt($src_img, $siX, $siY);
					$c3 = ImageColorAt($src_img, $siX2, $siY2);
					$c4 = ImageColorAt($src_img, $siX2, $siY);

					$r = $c1             +  $c2             +  $c3             +  $c4 >> 2 & 0xFF0000;
					$g = ($c1 & 0x00FF00) + ($c2 & 0x00FF00) + ($c3 & 0x00FF00) + ($c4 & 0x00FF00) >> 2 & 0x00FF00;
					$b = ($c1 & 0x0000FF) + ($c2 & 0x0000FF) + ($c3 & 0x0000FF) + ($c4 & 0x0000FF) >> 2;

				} else {

					$c1 = ImageColorsForIndex($src_img, ImageColorAt($src_img, $siX, $siY2));
					$c2 = ImageColorsForIndex($src_img, ImageColorAt($src_img, $siX, $siY));
					$c3 = ImageColorsForIndex($src_img, ImageColorAt($src_img, $siX2, $siY2));
					$c4 = ImageColorsForIndex($src_img, ImageColorAt($src_img, $siX2, $siY));

					$r = $c1['red']   + $c2['red']   + $c3['red']   + $c4['red'] << 14;
					$g = $c1['green'] + $c2['green'] + $c3['green'] + $c4['green'] <<  6;
					$b = $c1['blue']  + $c2['blue']  + $c3['blue']  + $c4['blue'] >>  2;

				}
				ImageSetPixel($dst_img, $dst_x + $x - $src_x, $dst_y + $y - $src_y, $r+$g+$b);
			}
		}
		return true;
	}


	static function ImageCreateFunction($x_size, $y_size) {
		$ImageCreateFunction = 'ImageCreate';
		if (self::gd_version() >= 2.0) {
			$ImageCreateFunction = 'ImageCreateTrueColor';
		}
		if (!function_exists($ImageCreateFunction)) {
			return (new phpthumb)->ErrorImage($ImageCreateFunction.'() does not exist - no GD support?');
		}
		if ($x_size <= 0 || $y_size <= 0) {
			return (new phpthumb)->ErrorImage('Invalid image dimensions: '.$ImageCreateFunction.'('.$x_size.', '.$y_size.')');
		}
		return $ImageCreateFunction(round($x_size), round($y_size));
	}


	static function ImageCopyRespectAlpha(&$dst_im, &$src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $opacity_pct=100) {
		$opacipct = $opacity_pct / 100;
		for ($x = $src_x; $x < $src_w; $x++) {
			for ($y = $src_y; $y < $src_h; $y++) {
				$RealPixel    = self::GetPixelColor($dst_im, $dst_x + $x, $dst_y + $y);
				$OverlayPixel = self::GetPixelColor($src_im, $x, $y);
				$alphapct = $OverlayPixel['alpha'] / 127;
				$overlaypct = (1 - $alphapct) * $opacipct;

				$newcolor = self::ImageColorAllocateAlphaSafe(
					$dst_im,
					round($RealPixel['red']   * (1 - $overlaypct)) + $OverlayPixel['red']   * $overlaypct,
					round($RealPixel['green'] * (1 - $overlaypct)) + $OverlayPixel['green'] * $overlaypct,
					round($RealPixel['blue']  * (1 - $overlaypct)) + $OverlayPixel['blue']  * $overlaypct,
					//$RealPixel['alpha']);
					0);

				ImageSetPixel($dst_im, $dst_x + $x, $dst_y + $y, $newcolor);
			}
		}
		return true;
	}

    /**
     * @param $old_width
     * @param $old_height
     * @param bool|float|int $new_width
     * @param bool|float|int $new_height
     * @return array|bool
     */
	static function ProportionalResize($old_width, $old_height, $new_width=false, $new_height=false) {
		$old_aspect_ratio = $old_width / $old_height;
        if ($new_width === false && $new_height === false) {
            return false;
        }

        if ($new_width === false) {
            $new_width = $new_height * $old_aspect_ratio;
        } elseif ($new_height === false) {
            $new_height = $new_width / $old_aspect_ratio;
        }
        $new_aspect_ratio = $new_width / $new_height;
		if ($new_aspect_ratio < $old_aspect_ratio) {
			// limited by width
			$new_height = $new_width / $old_aspect_ratio;
		} elseif ($new_aspect_ratio > $old_aspect_ratio) {
			// limited by height
			$new_width = $new_height * $old_aspect_ratio;
		}
		return array((int)round($new_width), (int)round($new_height));
	}


	static function FunctionIsDisabled($function) {
		static $DisabledFunctions = null;
		if ($DisabledFunctions === null) {
			$disable_functions_local  = explode(',',     strtolower(@ini_get('disable_functions')));
			$disable_functions_global = explode(',', strtolower(@get_cfg_var('disable_functions')));
			foreach ($disable_functions_local as $key => $value) {
				$DisabledFunctions[trim($value)] = 'local';
			}
			foreach ($disable_functions_global as $key => $value) {
				$DisabledFunctions[trim($value)] = 'global';
			}
		}
		return isset($DisabledFunctions[strtolower($function)]);
	}


	static function SafeExec($command) {
		static $AllowedExecFunctions = array();
		if (empty($AllowedExecFunctions)) {
			$AllowedExecFunctions = array('shell_exec'=>true, 'passthru'=>true, 'system'=>true, 'exec'=>true);
			foreach ($AllowedExecFunctions as $key => $value) {
				$AllowedExecFunctions[$key] = !self::FunctionIsDisabled($key);
			}
		}
		$command .= ' 2>&1'; // force redirect stderr to stdout
		foreach ($AllowedExecFunctions as $execfunction => $is_allowed) {
			if (!$is_allowed) {
				continue;
			}
			$returnvalue = false;
			switch ($execfunction) {
				case 'passthru':
				case 'system':
					ob_start();
					$execfunction($command);
					$returnvalue = ob_get_clean();
                break;

				case 'exec':
					$output = array();
					$returnvalue = implode("\n", $output);
					break;

				case 'shell_exec':
					ob_start();
					$returnvalue = $execfunction($command);
					ob_end_clean();
					break;
			}
			return $returnvalue;
		}
		return false;
	}


	static function ApacheLookupURIarray($filename) {
		// apache_lookup_uri() only works when PHP is installed as an Apache module.
		if (PHP_SAPI === 'apache') {
			//$property_exists_exists = function_exists('property_exists');
			$keys = array('status', 'the_request', 'status_line', 'method', 'content_type', 'handler', 'uri', 'filename', 'path_info', 'args', 'boundary', 'no_cache', 'no_local_copy', 'allowed', 'send_bodyct', 'bytes_sent', 'byterange', 'clength', 'unparsed_uri', 'mtime', 'request_time');
			if ($apacheLookupURIobject = @apache_lookup_uri($filename)) {
				$apacheLookupURIarray = array();
				foreach ($keys as $key) {
					$apacheLookupURIarray[$key] = @$apacheLookupURIobject->$key;
				}
				return $apacheLookupURIarray;
			}
		}
		return false;
	}


	static function gd_is_bundled() {
		static $isbundled = null;
		if ($isbundled === null) {
			$gd_info = gd_info();
			$isbundled = strpos($gd_info['GD Version'], 'bundled') !== false;
		}
		return $isbundled;
	}


	static function gd_version($fullstring=false) {
		static $cache_gd_version = [];
		if (empty($cache_gd_version)) {
			$gd_info = gd_info();
			if (preg_match('#bundled \((.+)\)$#i', $gd_info['GD Version'], $matches)) {
				$cache_gd_version[1] = $gd_info['GD Version'];  // e.g. "bundled (2.0.15 compatible)"
				$cache_gd_version[0] = (float) $matches[1];     // e.g. "2.0" (not "bundled (2.0.15 compatible)")
			} else {
				$cache_gd_version[1] = $gd_info['GD Version'];                       // e.g. "1.6.2 or higher"
				$cache_gd_version[0] = (float) substr($gd_info['GD Version'], 0, 3); // e.g. "1.6" (not "1.6.2 or higher")
			}
		}
		return $cache_gd_version[(int)$fullstring];
	}


	static function filesize_remote($remotefile, $timeout=10) {
		$size = false;
		$url = self::ParseURLbetter($remotefile);
		if ($fp = @fsockopen($url['host'], $url['port'] ? $url['port'] : 80, $errno, $errstr, $timeout)) {
			fwrite($fp, 'HEAD '.@$url['path'].@$url['query'].' HTTP/1.0'."\r\n".'Host: '.@$url['host']."\r\n\r\n");
			if (PHP_VERSION_ID >= 40300) {
				stream_set_timeout($fp, $timeout);
			}
			while (!feof($fp)) {
				$headerline = fgets($fp, 4096);
				if (preg_match('#^Content-Length: (.*)#i', $headerline, $matches)) {
					$size = (int)$matches[1];
					break;
				}
			}
			fclose ($fp);
		}
		return $size;
	}


	static function filedate_remote($remotefile, $timeout=10) {
		$date = false;
		$url = self::ParseURLbetter($remotefile);
		if ($fp = @fsockopen($url['host'], $url['port'] ? $url['port'] : 80, $errno, $errstr, $timeout)) {
			fwrite($fp, 'HEAD '.@$url['path'].@$url['query'].' HTTP/1.0'."\r\n".'Host: '.@$url['host']."\r\n\r\n");
			if (PHP_VERSION_ID >= 40300) {
				stream_set_timeout($fp, $timeout);
			}
			while (!feof($fp)) {
				$headerline = fgets($fp, 4096);
				if (preg_match('#^Last-Modified: (.*)#i', $headerline, $matches)) {
					$date = strtotime($matches[1]) - date('Z');
					break;
				}
			}
			fclose ($fp);
		}
		return $date;
	}


	static function md5_file_safe($filename) {
		// md5_file() doesn't exist in PHP < 4.2.0
		if (function_exists('md5_file')) {
			return md5_file($filename);
		}
		if ($fp = @fopen($filename, 'rb')) {
			$rawData = '';
			do {
				$buffer = fread($fp, 8192);
				$rawData .= $buffer;
			} while ($buffer !== '');
			fclose($fp);
			return md5($rawData);
		}
		return false;
	}


	static function nonempty_min() {
		$arg_list = func_get_args();
		$acceptable = array();
		foreach ($arg_list as $arg) {
			if ($arg) {
				$acceptable[] = $arg;
			}
		}
		return min($acceptable);
	}


	static function LittleEndian2String($number, $minbytes=1) {
		$intstring = '';
		while ($number > 0) {
			$intstring .= chr($number & 255);
			$number >>= 8;
		}
		return str_pad($intstring, $minbytes, "\x00", STR_PAD_RIGHT);
	}

	static function OneOfThese() {
		// return the first useful (non-empty/non-zero/non-false) value from those passed
		$arg_list = func_get_args();
		foreach ($arg_list as $key => $value) {
			if ($value) {
				return $value;
			}
		}
		return false;
	}

	static function CaseInsensitiveInArray($needle, $haystack) {
		$needle = strtolower($needle);
		foreach ($haystack as $key => $value) {
			if (!is_array($value) && $needle === strtolower($value)) {
                return true;
            }
        }
		return false;
	}

	static function URLreadFsock($host, $file, &$errstr, $successonly=true, $port=80, $timeout=10) {
		if (!function_exists('fsockopen') || self::FunctionIsDisabled('fsockopen')) {
			$errstr = 'fsockopen() unavailable';
			return false;
		}
		if ($fp = @fsockopen($host, $port, $errno, $errstr, $timeout)) {
			$out  = 'GET '.$file.' HTTP/1.0'."\r\n";
			$out .= 'Host: '.$host."\r\n";
			$out .= 'Connection: Close'."\r\n\r\n";
			fwrite($fp, $out);

			$isHeader = true;
			$Data_body   = '';
			$header_newlocation = '';
			while (!feof($fp)) {
				$line = fgets($fp, 1024);
				if (!$isHeader) {
					$Data_body .= $line;
				}
				if (preg_match('#^HTTP/[.0-9]+ ([0-9]+) (.+)$#i', rtrim($line), $matches)) {
					list(, $errno, $errstr) = $matches;
					$errno = (int)$errno;
				} elseif (preg_match('#^Location: (.*)$#i', rtrim($line), $matches)) {
					$header_newlocation = $matches[1];
				}
				if ($isHeader && $line === "\r\n") {
					$isHeader = false;
					if ($successonly) {
						switch ($errno) {
							case 200:
								// great, continue
								break;

							default:
								$errstr = $errno.' '.$errstr.($header_newlocation ? '; Location: '.$header_newlocation : '');
								fclose($fp);
								return false;
								break;
						}
					}
				}
			}
			fclose($fp);
			return $Data_body;
		}
		return null;
	}

	static function CleanUpURLencoding($url, $queryseperator='&') {
		if (stripos($url, 'http') !== 0) {
			return $url;
		}
		$parse_url = self::ParseURLbetter($url);
		$pathelements = explode('/', $parse_url['path']);
		$CleanPathElements = array();
		$TranslationMatrix = array(' '=>'%20');
		foreach ($pathelements as $key => $pathelement) {
			$CleanPathElements[] = strtr($pathelement, $TranslationMatrix);
		}
		foreach ($CleanPathElements as $key => $value) {
			if ($value === '') {
				unset($CleanPathElements[$key]);
			}
		}

		$queries = explode($queryseperator, isset($parse_url['query']) ? $parse_url['query'] : '');
		$CleanQueries = array();
		foreach ($queries as $key => $query) {
			@list($param, $value) = explode('=', $query);
			$CleanQueries[] = strtr($param, $TranslationMatrix).($value ? '='.strtr($value, $TranslationMatrix) : '');
		}
		foreach ($CleanQueries as $key => $value) {
			if ($value === '') {
				unset($CleanQueries[$key]);
			}
		}

		$cleaned_url  = $parse_url['scheme'].'://';
		$cleaned_url .= @$parse_url['username'] ? $parse_url['host'].(@$parse_url['password'] ? ':'.$parse_url['password'] : '').'@' : '';
		$cleaned_url .= $parse_url['host'];
		$cleaned_url .= !empty($parse_url['port']) && $parse_url['port'] != 80 ? ':'.$parse_url['port'] : '';
		$cleaned_url .= '/'.implode('/', $CleanPathElements);
		$cleaned_url .= @$CleanQueries ? '?'.implode($queryseperator, $CleanQueries) : '';
		return $cleaned_url;
	}

	static function ParseURLbetter($url) {
		$parsedURL = @parse_url($url);
		if (!@$parsedURL['port']) {
			switch (strtolower(@$parsedURL['scheme'])) {
				case 'ftp':
					$parsedURL['port'] = 21;
					break;
				case 'https':
					$parsedURL['port'] = 443;
					break;
				case 'http':
					$parsedURL['port'] = 80;
					break;
			}
		}
		return $parsedURL;
	}

	static function SafeURLread($url, &$error, $timeout=10) {
		$error = '';

		$parsed_url = self::ParseURLbetter($url);
		$alreadyLookedAtURLs[trim($url)] = true;

		while (true) {
			$tryagain = false;
			$rawData = self::URLreadFsock(@$parsed_url['host'], @$parsed_url['path'].'?'.@$parsed_url['query'], $errstr, true, @$parsed_url['port'] ? @$parsed_url['port'] : 80, $timeout);
			if (preg_match('#302 [a-z ]+; Location: (http.*)#i', $errstr, $matches)) {
				$matches[1] = trim(@$matches[1]);
				if (!@$alreadyLookedAtURLs[$matches[1]]) {
					// loop through and examine new URL
					$error .= 'URL "'.$url.'" redirected to "'.$matches[1].'"';

					$tryagain = true;
					$alreadyLookedAtURLs[$matches[1]] = true;
					$parsed_url = self::ParseURLbetter($matches[1]);
				}
			}
			if (!$tryagain) {
				break;
			}
		}

        if ($rawData === false) {
            $error .= 'Error opening "'.$url.'":'."\n\n".$errstr;
            return false;
        }

        if ($rawData === null) {
            // fall through
            $error .= 'Error opening "'.$url.'":'."\n\n".$errstr;
        } else {
            return $rawData;
        }

        if (function_exists('curl_version') && !self::FunctionIsDisabled('curl_exec')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
			$rawData = curl_exec($ch);
			curl_close($ch);
			if ($rawData !== '') {
				$error .= 'CURL succeeded ('.strlen($rawData).' bytes); ';
				return $rawData;
			}
			$error .= 'CURL available but returned no data; ';
		} else {
			$error .= 'CURL unavailable; ';
		}

		if (@ini_get('allow_url_fopen')) {
			$rawData = '';
			$error_fopen = '';
			ob_start();
			if ($fp = fopen($url, 'rb')) {
				do {
					$buffer = fread($fp, 8192);
					$rawData .= $buffer;
				} while ($buffer !== '');
				fclose($fp);
			} else {
				$error_fopen .= trim(strip_tags(ob_get_contents()));
			}
			ob_end_clean();
			$error .= $error_fopen;
			if (!$error_fopen) {
				$error .= '; "allow_url_fopen" succeeded ('.strlen($rawData).' bytes); ';
				return $rawData;
			}
			$error .= '; "allow_url_fopen" enabled but returned no data ('.$error_fopen.'); ';
		} else {
			$error .= '"allow_url_fopen" disabled; ';
		}

		return false;
	}

	static function EnsureDirectoryExists($dirname) {
		$directory_elements = explode(DIRECTORY_SEPARATOR, $dirname);
		$startoffset = !$directory_elements[0] ? 2 : 1;  // unix with leading "/" then start with 2nd element; Windows with leading "c:\" then start with 1st element
		$open_basedirs = preg_split('#[;:]#', ini_get('open_basedir'));
		foreach ($open_basedirs as $key => $open_basedir) {
			if (preg_match('#^'.preg_quote($open_basedir).'#', $dirname) && strlen($dirname) > strlen($open_basedir)) {
				$startoffset = count(explode(DIRECTORY_SEPARATOR, $open_basedir));
				break;
			}
		}
		$endoffset = count($directory_elements);
		for ($i = $startoffset; $i <= $endoffset; $i++) {
			$test_directory = implode(DIRECTORY_SEPARATOR, array_slice($directory_elements, 0, $i));
			if (!$test_directory) {
				continue;
			}
			if (!@is_dir($test_directory)) {
				if (@file_exists($test_directory)) {
					// directory name already exists as a file
					return false;
				}
                if (!mkdir($test_directory, 0755) && !is_dir($test_directory)) {
                    //throw new \RuntimeException(sprintf('Directory "%s" was not created', $test_directory));
                }
				@chmod($test_directory, 0755);
				if (!@is_dir($test_directory) || !@is_writable($test_directory)) {
					return false;
				}
			}
		}
		return true;
	}


	static function GetAllFilesInSubfolders($dirname) {
		$AllFiles = array();
		$dirname = rtrim(realpath($dirname), '/\\');
		if ($dirhandle = @opendir($dirname)) {
			while (($file = readdir($dirhandle)) !== false) {
				$fullfilename = $dirname.DIRECTORY_SEPARATOR.$file;
				if (is_file($fullfilename)) {
					$AllFiles[] = $fullfilename;
				} elseif (is_dir($fullfilename)) {
					switch ($file) {
						case '.':
						case '..':
							break;

						default:
							$AllFiles[] = $fullfilename;
							$subfiles = self::GetAllFilesInSubfolders($fullfilename);
							foreach ($subfiles as $filename) {
								$AllFiles[] = $filename;
							}
							break;
					}
				}
            }
			closedir($dirhandle);
		}
		sort($AllFiles);
		return array_unique($AllFiles);
	}


	static function SanitizeFilename($filename) {
		$filename = preg_replace('/[^'.preg_quote(' !#$%^()+,-.;<>=@[]_{}').'a-zA-Z0-9]/', '_', $filename);
		if (PHP_VERSION_ID >= 40100) {
			$filename = trim($filename, '.');
		}
		return $filename;
	}

}
////////////// END: class phpthumb_functions //////////////