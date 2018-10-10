<?php

    if ($argc > 1)
    {
        $check = preg_replace("/[\d\s.*+-X\^=]/", "", $argv[1]);
        if (!empty($check[0]) || preg_match_all("/[X]\^-/", $argv[1], $check2))
        {
            echo "Syntax error\n";
            exit();
        }
        if (preg_match_all("/[X]\^\b[3-9]/", $argv[1], $check2))
        {
            echo "The polynomial degree is stricly greater than 2, I can't solve.\n";
            exit();
        }
        $equation = explode("=", $argv[1]);
        $dir = "left";
        $tmp = 0;
        for($i = 0; $i <= 1; $i++) {
            for($j = 0; $j <= 2; $j++) {
                $equation[$tmp] = simplifie($equation[$tmp], $j);
                echo "degree ".$j." ".$dir." side of the equation simplification:  " . $equation[0] . " = " . $equation[1] . "\n";
            }
            $dir = "right";
            $tmp++;
        }
        for ($i = 0; $i <= 2; $i++) {
            $equation = transposition($equation[0], $equation[1], $i);
            if (!isset($equation[1])|| $i == 2)
                $equation[1] = "0";
            echo "degree ".$i." transfert:     " . $equation[0] . " = " . $equation[1] . "\n";
        }
        echo "\n\nreduced form: " . $equation[0] . " = " . $equation[1] . "\n\n";
        if (preg_match_all("/[X]\^([3-9])/", $argv[1], $check2))
        {
	        echo "Polynomial degree: ". $check2[1][0] . "\nThe polynomial degree is stricly greater than 2, I can't solve.\n";
	        exit();
        }
        $tmp = preparer($equation);
        if ($tmp["a"])
        {
            echo "Polynomial degree: 2\n";
            $delta = getDeltaSecondDegree($tmp);
            echo "Discriminant: " . $delta . "\n";
            getSolutionsSecondDegree($tmp, $delta);
        }
        else if ($tmp["b"])
        {
            echo "Polynomial degree: 1\n";
            getSolutionsFirstDegree($tmp);
        }
        else if ($tmp["c"] == 0)
        {
            echo "Every real numbers are the solution\n";
        }
        else if ($tmp["c"] < 0)
        {
            echo "There is no solution\n";
        }

    }
    function 		preparer($equation)
        {
            preg_match_all("/(([\+\-]?)\s?(\d+[.]\d+)\s?[*]\s?[X]\^0)/", $equation[0], $deg0);
            if (empty($deg0[2][0]))
                preg_match_all("/(([\+\-]?)\s?(\d+[.]?\d?+)\s?[*]\s?[X]\^0)/", $equation[0], $deg0);
            preg_match_all("/(([\+\-]?)\s?(\d+[.]\d+)\s?[*]\s?[X]\^1)/", $equation[0], $deg1);
            if (empty($deg1[2][0]))
                preg_match_all("/(([\+\-]?)\s?(\d+[.]?\d?+)\s?[*]\s?[X]\^1)/", $equation[0], $deg1);
            preg_match_all("/(([\+\-]?)\s?(\d+[.]\d+)\s?[*]\s?[X]\^2)/", $equation[0], $deg2);
            if (empty($deg2[2][0]))
                preg_match_all("/(([\+\-]?)\s?(\d+[.]?\d?+)\s?[*]\s?[X]\^2)/", $equation[0], $deg2);
            preg_match_all("/(([\+\-]?)\s?(\d+[.]?\d?+)\s?[*]\s?[X]\^([^1^2^0]))/", $equation[0], $check);
            if (!empty($check[2][0]))
            {
		        echo "Polynomial degree: " . $check[4][0] . "\nThe polynomial degree is stricly greater than 2, I can't solve.\n";
		        exit();
	        }
            $c = implode(Array($deg0[2][0], $deg0[3][0]));
            $b = implode(Array($deg1[2][0], $deg1[3][0]));
            $a = implode(Array($deg2[2][0], $deg2[3][0]));
            return Array("a" => $a, "b" => $b,"c" =>  $c);
        }

        function 		getDeltaSecondDegree($elems)
        {
            $one = $elems["b"] * $elems["b"];
            $two = 4 * $elems["a"] * $elems["c"];
            $delta = $one - $two;
            return $delta;
        }

        function 		getSolutionsSecondDegree($elems, $delta)
        {
            if ($delta > 0)
            {
                $one = - $elems["b"] - sqrt($delta);
                $two = 2 * $elems["a"];
                $solution1 = $one / $two;
                $one = - $elems["b"] + sqrt($delta);
                $solution2 = $one / $two;
                echo "Discriminant is strictly positive, the two solutions are:\n" . $solution1 . "\n" . $solution2 . "\n";
            }
            else if ($delta == 0)
            {
                $one = 2 * $elems["a"];
                $solution =  - ($elems["b"] / $one);
                echo "Discriminant equals 0, the solution is:\n" . $solution . "\n";
            }
            else if ($delta < 0)
            {
                echo "Discriminant is strictly negative, the two solutions are:\n";
                $one = 2 * $elems["a"];
                $two = - $elems["b"] / $one;
                $three =  sqrt(-$delta) / $one;
                echo round($two, 6) . " + " . round($three, 6) . "i\n";
                echo round($two, 6) . " - " . round($three, 6) . "i\n";
            }
        }

        function 		getSolutionsFirstDegree($elems)
        {
            if ($elems["c"] > 0)
                $one = preg_replace("/\+/", "-", $elems["c"]);
            else if ($elems["c"] < 0)
                $one = preg_replace("/\-/", "+", $elems["c"]);
            $solution = - $one / $elems["b"];
            echo "The solution is:\n" . $solution . "\n";
            return ;
        }

        function 		simplifie($str, $deg)
        {
            $str = trim($str);
            $reg = "/(([\+\-]?)\s?(\d+[.]?\d?+)\s?[*]\s?[X]\^" . $deg . ")/";
            
            while (preg_match_all($reg, $str, $matches) > 1 && $matches)
            {
                if ($matches[0][0] && $matches[0][1])
                {
                    $sign = $matches[2][0];
                    $sign2 = $matches[2][1];
                    if ($matches[2][1] == '+')
                    {
                        if ($matches[2][0] == '-')
                            $result = $matches[3][0] - $matches[3][1];
                        else
                            $result = $matches[3][0] + $matches[3][1];
                        $str = applySimplification($result, $sign, $sign2, $matches, $deg, $str);
                    }
                    else if ($matches[2][1] == '-')
                    {
                        $result = $matches[3][0] - $matches[3][1];
                        if ($result < 0 && $matches[2][0] == '+')
                        {
                            $matches[2][0] = '-';
                            preg_match("/\d+[.]?\d?+/", $result, $tmp);
                            $result = $tmp[0];
                        }
                        else if ($matches[2][0] == '-')
                            $result = $matches[3][0] + $matches[3][1];
                        $str = applySimplification($result, $sign, $sign2, $matches, $deg, $str);
                    }
                }
                
            }
            return $str;
        }
        function 		applySimplification($result, $sign, $sign2, $matches, $deg, $str)
        {
            if ($sign2)
                $regex2 = "/([\\" . $sign2 . "]\s?" . $matches[3][1] . "\s?[*]\s?[X]\^" . $deg . ")/";
            else
                $regex2 = "/(\s?" . $matches[3][1] . "\s?[*]\s?[X]\^" . $deg . ")/";
            $str = preg_replace($regex2, "", $str);
            if ($sign)
                $regex = "/([\\" . $sign . "]\s?" . $matches[3][0] . "\s?[*]\s?[X]\^" . $deg . ")/";
            else
                $regex = "/(\s?" . $matches[3][0] . "\s?[*]\s?[X]\^" . $deg . ")/";
            if ($result != 0)
                $str = preg_replace($regex, $matches[2][0] . " " . $result . " * X^" . $deg, $str);
            else
                $str = preg_replace($regex, "", $str);	
            return $str;
        }
        function 		transposition($str, $str2, $deg)
        {
            $str = trim($str);
            $reg = "/([\+\-]?)\s?((\d+[.]?\d?+)\s?[*]\s?[X]\^" . $deg . ")/";
            preg_match_all($reg, $str, $matches);
            $reg = "/([\+\-]?)\s?((\d+[.]?\d?+)\s?[*]\s?[X]\^" . $deg . ")/";
            preg_match_all($reg, $str2, $matches2);
            print_r($str);
            print_r($matches2);
            if ($matches && $matches2)
            {
                $sign = $matches[1][0];
                $sign2 = $matches2[1][0];
                if (!empty($matches[0][0]) && !empty($matches2[0][0]))
                {	
                    if ($matches2[1][0] == '+' || !$matches2[1][0])
                    {
                        $result = $matches[3][0] - $matches2[3][0];
                        if ($result < 0 && ($matches[1][0] == '+' || !$matches[1][0]))
                        {
                            $matches[1][0] = '-';
                            preg_match("/\d+[.]?\d?+/", $result, $tmp);
                            $result = $tmp[0];
                        }
                        else if ($matches[1][0] == '-')
                        {
                            $result = $matches[3][0] + $matches2[3][0];	
                        }	   				
                        $str = applyTransfert($result, $sign, $sign2, $matches, $matches2, $deg, $str, $str2);
                    }
                    else if ($matches2[1][0] == '-')
                    {
                        $result = $matches[3][0] + $matches2[3][0];
                        $str = applyTransfert($result, $sign, $sign2, $matches, $matches2, $deg, $str, $str2);
                    }
                    return $str;
                }
                else if (!empty($matches2[0][0]))
                {
                    if ($sign2)
                        $regex2 = "/([\\" . $sign2 . "]\s?" . $matches2[3][0] . "\s?[*]\s?[X]\^" . $deg . ")/";
                    else
                        $regex2 = "/(\s?" . $matches2[3][0] . "\s?[*]\s?[X]\^" . $deg . ")/";
                    $str2 = preg_replace($regex2, "0", $str2);
                    if ((!$sign2 || $sign2 == '+') && $matches2[3][0] != '0')
                        $str = implode(" - ", Array($str, $matches2[2][0]));
                    else if ($sign2 == '-' && $matches2[3][0] != '0')
                        $str = implode(" + ", Array($str, $matches2[2][0]));
                    return Array($str, $str2);
                }
                else if (!isset($str2))
                    $str2 = "0";	   	
            }
            return Array($str, $str2);
        }
        function 		applyTransfert($result, $sign, $sign2, $matches, $matches2, $deg, $str, $str2)
        {
            if ($sign2)
                $regex2 = "/([\\" . $sign2 . "]\s?" . $matches2[3][0] . "\s?[*]\s?[X]\^" . $deg . ")/";
            else
                $regex2 = "/(\s?" . $matches2[3][0] . "\s?[*]\s?[X]\^" . $deg . ")/";
            $str2 = preg_replace($regex2, "", $str2);
            if (!$str2)
                $str2 = "0";
            if ($sign)
                $regex = "/([\\" . $sign . "]\s?" . $matches[3][0] . "\s?[*]\s?[X]\^" . $deg . ")/";
            else
                $regex = "/(\s?" . $matches[3][0] . "\s?[*]\s?[X]\^" . $deg . ")/";
            if ($result != 0)
                $str = preg_replace($regex, $matches[1][0] . " " . $result . " * X^" . $deg, $str); 
            else
                $str = preg_replace($regex, "", $str); 
            return Array($str, $str2);
        }
?>