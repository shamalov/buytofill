<?php
    function N2A($toN2A){
        $alphabet = range('A', 'Z');
        $result = '';
        $toN2A--;
        $position = [0, 0, 0, 0, 0];
        for ($i = 4; $i >= 0; $i--){ 
            $position[$i] = $toN2A % 26; 
            $toN2A = floor($toN2A / 26); 
        }
        foreach ($position as $pos){ 
            $result .= $alphabet[$pos]; 
        }
        return $result;
    }

    function A2N($toA2N){
        $alphabet = range('A','Z');
        $toA2N = str_split($toA2N);
        $result = 0;
        $power = count($toA2N) - 1;
        foreach ($toA2N as $char){
            $position = array_search($char, $alphabet);
            $result += ($position + 1) * (26 ** $power);
            $power--;
        }
        return $result-475254;
    }
?>