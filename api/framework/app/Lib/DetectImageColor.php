<?php

namespace App\Lib;

class DetectImageColor
{

	public  $hex         = "";

	private $pathToImg   = "";

	private $type        = "";

	private $type_string = "";

	/**
	 * Constructor of the class
	 *
	 * @param             $path_img
	 * @param bool        $debug
	 */
	public function __construct($path_img, $debug = false)
	{
		$this->pathToImg = $path_img;
		$this->type      = exif_imagetype($path_img);

		if( $this->type === IMAGETYPE_JPEG )
		{
			$image             = imagecreatefromjpeg($path_img);
			$this->type_string = 'IMAGETYPE_JPEG';
		}
		elseif( $this->type === IMAGETYPE_PNG )
		{
			$image             = imagecreatefrompng($path_img);
			$this->type_string = 'IMAGETYPE_PNG';
		}
		else
		{
			$this->type_string = 'IMAGETYPE OTHER';
		}

		$imgcreatc = imagecreatetruecolor(1, 1);

		imagecopyresampled($imgcreatc, $image, 0, 0, 0, 0, 1, 1, imagesx($image), imagesy($image));

		$this->hex = '#' . dechex(imagecolorat($imgcreatc, 0, 0));

		imagedestroy($imgcreatc);

		if( $debug )
		{
			if( $this->type === IMAGETYPE_JPEG or $this->type === IMAGETYPE_PNG )
			{
				echo $this->getColorBackground();
			}
			else
			{
				echo $this->type_string;
			}
		}
	}

	public function getImgType(): string
	{
		return $this->type;
	}

	public function getImage(): string
	{
		return '<img src="' . $this->pathToImg . '" alt="" />';
	}

	public function getColorBackground(): string
	{
		return '<body style="background:' . $this->hex . ';">' . $this->getImage() . '<br />' . $this->type_string . '</body>';
	}

	public function getHex(): string
	{
		return $this->hex;
	}
}