<?php
namespace modules;

class ContentGenerator
{
    /**
     * generate HEXDECIMAL pastel color value (no #)
     */
    public static function generatePastelColor() {
        // Extract the last two digits from the user ID
        $lastThreeDigits = rand(0, 999);
        // Scale the last two digits to a range of 0 to 100
        $redscale = 1;
        $bluescale = 1;
        $greenscale = 1;
        $minValue = 205;
        $startValue = $lastThreeDigits;
        if ($startValue > 255){
            $startValue = $lastThreeDigits % 255;
        }
        if ( $lastThreeDigits > 255 && $lastThreeDigits < 510){
            $redscale++;
        }
        if ($lastThreeDigits >= 510 && $lastThreeDigits < 765){
            $bluescale++;
        }
        if ($lastThreeDigits >= 765){
            $greenscale++;
        }

        if ($startValue < 80){
            $redscale++;
        } else if ($startValue < 160){
            $bluescale++;
        } else {
            $greenscale++;
        }
        
        $coefficient = ($startValue / 255) * (255 - $startValue);
        // Calculate the RGB values based on the scaled value
        $red =   $minValue + ($coefficient * $redscale);
        $green = $minValue + ($coefficient * $greenscale);
        $blue =  $minValue + ($coefficient * $bluescale);
    
        if ($red > 255){
            $red = rand(180, 245);
        }
        if ($green > 255){
            $green = rand(180, 245);
        }
        if ($blue > 255){
            $blue = rand(180, 245);
        }

        // Convert RGB values to hexadecimal format
        $hexColor = sprintf("%02x%02x%02x", $red, $green, $blue);
        return $hexColor;
    }


}