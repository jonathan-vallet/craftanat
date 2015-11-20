<?php
/**
 * Color class
 */
class ColorTool extends SingletonModel
{
	/**
	 * Convert a color string from hexadecimal to RGB format
	 *
	 * @param string $color an hexadecimal color (#FFFFFF, #FFF, AAAAAA are available formats)
	 * @return array|boolean an array with r,g,b result, or false if color format was not correct
	 */
	public static function hexaToRGB($color){
		if ($color[0] == '#')
			$color = StringTool::substr($color, 1);

		if (StringTool::strlen($color) == 6)
			list($r, $g, $b) = array($color[0].$color[1],
									 $color[2].$color[3],
									 $color[4].$color[5]);
		elseif (StringTool::strlen($color) == 3)
			list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
		else
			return false;

		$r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

		return array($r, $g, $b);
	}
	
	/**
	 * Insert a color to the palet or return the color if the color exist
	 *
	 * @param image $pic the picture where we work on it
	 * @param int $c1 red part of the color
	 * @param int $c2 green part of the color
	 * @param int $c3 blue part of the color
	 * @return color the color that we want
	 */
	public static function createcolor($pic,$c1,$c2,$c3) {
          //get color from palette
          $color = imagecolorexact($pic, $c1, $c2, $c3);
          if($color==-1) {
               //color does not exist...
               //test if we have used up palette
               if(imagecolorstotal($pic)>=255) {
                    //palette used up; pick closest assigned color
                    $color = imagecolorclosest($pic, $c1, $c2, $c3);
               } else {
                    //palette NOT used up; assign new color
                    $color = imagecolorallocate($pic, $c1, $c2, $c3);
               }
          }
          return $color;
     }

}
?>