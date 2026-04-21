<?php

namespace App\Services;

use RuntimeException;
use ZipArchive;

/**
 * Takes a square source image and produces a standard favicon set.
 * Uses PHP's GD extension.
 */
class FaviconGenerator
{
	/** Standard sizes every favicon set should include. */
	public const STANDARD_SIZES = [16, 32, 48, 180, 192, 512];

	/** @return array<int,string> map of size → path-to-png inside $outputDir */
	public function generate(string $sourcePath, string $outputDir, array $sizes = self::STANDARD_SIZES): array
	{
		if (! extension_loaded('gd')) {
			throw new RuntimeException('GD extension is not installed');
		}
		if (! is_file($sourcePath) || ! is_readable($sourcePath)) {
			throw new RuntimeException('Source image not readable');
		}

		$info = getimagesize($sourcePath);
		if (! $info) {
			throw new RuntimeException('Source is not a valid image');
		}

		$src = $this->loadImage($sourcePath, (int) $info[2]);
		$srcW = imagesx($src);
		$srcH = imagesy($src);

		if (! is_dir($outputDir)) {
			mkdir($outputDir, 0755, true);
		}

		$produced = [];
		foreach ($sizes as $size) {
			$dst = imagecreatetruecolor($size, $size);
			imagealphablending($dst, false);
			imagesavealpha($dst, true);
			$transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
			imagefill($dst, 0, 0, $transparent);

			// Preserve aspect — center on transparent canvas
			$ratio = min($size / $srcW, $size / $srcH);
			$newW = (int) round($srcW * $ratio);
			$newH = (int) round($srcH * $ratio);
			$offX = (int) round(($size - $newW) / 2);
			$offY = (int) round(($size - $newH) / 2);

			imagecopyresampled($dst, $src, $offX, $offY, 0, 0, $newW, $newH, $srcW, $srcH);

			$path = $outputDir . '/favicon-' . $size . 'x' . $size . '.png';
			imagepng($dst, $path, 9);
			imagedestroy($dst);
			$produced[$size] = $path;
		}

		imagedestroy($src);

		// Also write a 32x32 favicon.ico (PNG inside ICO container) for broad support.
		if (isset($produced[32])) {
			$icoPath = $outputDir . '/favicon.ico';
			if ($this->writeIcoFromPng($produced[32], $icoPath)) {
				$produced['ico'] = $icoPath;
			}
		}

		return $produced;
	}

	/** @param array<int|string,string> $files */
	public function zip(array $files, string $zipPath): string
	{
		$zip = new ZipArchive();
		if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
			throw new RuntimeException('Cannot open zip for writing');
		}
		foreach ($files as $file) {
			$zip->addFile($file, basename($file));
		}
		$zip->close();
		return $zipPath;
	}

	private function loadImage(string $path, int $type)
	{
		return match ($type) {
			IMAGETYPE_PNG => imagecreatefrompng($path),
			IMAGETYPE_JPEG => imagecreatefromjpeg($path),
			IMAGETYPE_GIF => imagecreatefromgif($path),
			IMAGETYPE_WEBP => imagecreatefromwebp($path),
			default => throw new RuntimeException('Unsupported image type (use PNG, JPG, GIF or WEBP)'),
		};
	}

	/** Minimal PNG-in-ICO container writer — works for modern browsers. */
	private function writeIcoFromPng(string $pngPath, string $outPath): bool
	{
		$png = @file_get_contents($pngPath);
		if ($png === false) {
			return false;
		}

		$dim = getimagesize($pngPath);
		if (! $dim) {
			return false;
		}
		$w = $dim[0] < 256 ? $dim[0] : 0;
		$h = $dim[1] < 256 ? $dim[1] : 0;

		$iconDir = pack('v3', 0, 1, 1);
		$iconDirEntry = pack('C4v2V2',
			$w, $h,
			0, 0,           // palette + reserved
			1, 32,          // planes + bpp
			strlen($png),   // size
			22              // offset = sizeof(iconDir)+sizeof(entry)=6+16
		);

		return @file_put_contents($outPath, $iconDir . $iconDirEntry . $png) !== false;
	}
}
