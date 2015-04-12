
<!DOCTYPE html>
<html>
<body>
<?php
$input=(isset($_POST['inputText'])) ? htmlentities($_POST['inputText']): '';
$code=array();
$slice = array();
$eCriteria=array();
$lCrieriA=array();
$marked=array();
if($input!="")
{
    $input=html_entity_decode($input);
    $line=strtok($input, "\n");
    $statements = array();
    $criteria=array();
    $startArr=array();
    $endArr=array();
    $slice = array();
    $lMap=array();
    $visited=array();
    $init=array();
    $mFlag=0;
    while ($line!=false)
    {
        if(strlen($line) > 1){
            array_push($statements,$line);
             
        }
        $line=getNextLine();
    }
    $totalLines = count($statements);
    for($x = 0; $x < $totalLines; $x++) {
        ////echo $x;
        ////echo ": ";
        ////echo $statements[$x];
        ////echo "<br>";
        $visited[$x]=0;
        $marked[$x]=0;
    }
    $printStmt=$totalLines-1;
    array_push($slice,$printStmt);
    $count=0;
    $index;
    for($y=0;$y<strlen($statements[$printStmt]);$y++)
    {
         
        if($statements[$printStmt][$y]=='"')
        {
            $count++;
        }
        if($count==2)
        {
            $index = $y;
            break;
        }
    }
    $index+=2;
    trim($statements[$printStmt]);
    $newStr=substr($statements[$printStmt],$index,-2);
    $newStr .= ",";
    ////echo "Criteria : ";
    $var=strtok($newStr, ",");
    while ($var!=false){
        ////echo $var." ";
        array_push($criteria,$var);
        array_push($init,$var);
        $var = getNextVar();
    }
    ////echo "<br>";
    //
    $bFlag=0;
    $start;
    $tStart;
    $tEnd;
    $end;
    $pFlag=0;
    $qFlag=0;
    for($x = 0; $x < $totalLines; $x++)
    {
        if($statements[$x][0]=='{')
        {
            $bFlag=1;
            if($pFlag==1)
            {
                $tStart=$x;
                $qFlag=1;
                $pFlag=0;//
            }
            else
            {
                $start=$x;
            // modification
                $pFlag=1;
            }
        }
        else if($statements[$x][0]=='}')
        {
             
            if($pFlag==1)
            {
                $end=$x;
                for($y = $start; $y <=$end; $y++)
                {
                    $startArr[$y]=$start;
                    $endArr[$y]=$end;
                }
                $pFlag=0;
            }
            else
            {
                $tEnd=$x;
                $pFlag=1;//working
            }
            $bFlag=0;
             
        }
        else
        {
            if($bFlag==0)
            {
                $startArr[$x]=$x;
                $endArr[$x]=$x;
            }
        }
    }
    if($qFlag==1)
    {
        ////echo "here too";
        for($y = $tStart; $y <=$tEnd; $y++)
        {
            $startArr[$y]=$tStart;
            $endArr[$y]=$tEnd;
        }
    }
    for($x=0;$x<count($statements);$x++)
    {
        //echo $startArr[$x]." ".$endArr[$x]."<br>";
    }
    $code = $statements;
    $lmap = array(array());
    $rmap = array(array());
    for($x = 0; $x< $totalLines-1; $x++){
        $lval;
        $rval;
        $val = array();
        $temp = "";
        $flag = 0;
        $len=strlen($statements[$x]);
         
// for parsing start
        if(strncmp(trim($statements[$x]), "int", 3)==0)
        {
            $newStr=substr($statements[$x],3,-2);
            $lMap[$x] = "_";
            //echo $newStr."<br>";
            $newStr .= ",";
            $var=strtok($newStr, ",");
            $temp = "";
            while ($var!=false)
            {
                for($y = 0; $y < strlen($newStr); $y++){
                    if(ctype_alpha($newStr[$y]))
                    {
                        $temp .=$newStr[$y];
                    }
                    else
                    {
                        if($temp!="")
                        {
                            $rmap[$x][] = $temp;
                             
                        }
                        $temp="";
                    }
                }
                $var = getNextVar();
            }
             
        }
        else if(strncmp(trim($statements[$x]), "printf", 6)==0){
            if(empty($rmap[$x])){
                    $rmap[$x][] = "-";
                    $lMap[$x] = "-";
            }
        }
        else if(strncmp(trim($statements[$x]), "scanf", 5)==0){
             
            $count = 0;
            $index = 0;
             
            for($y=0;$y<strlen($statements[$x]);$y++)
            {              
                if($statements[$x][$y]=='"')
                {
                    $count++;
                }
                if($count==2)
                {
                    $index = $y;
                    break;
                }
            }
            $index+=2;
            trim($statements[$x]);
            $newStr=substr($statements[$x],$index,-2);
            $temp = "";
            $newStr .= ",";
            $var=strtok($newStr, ",");
             
            while ($var!=false){
                for($y = 0; $y < strlen($newStr); $y++){
                    if(ctype_alpha($newStr[$y]))
                    {
                        $temp .=$newStr[$y];
                    }
                    else
                    {
                        if($temp!="")
                        {
                            $rmap[$x][] = $temp;
                            //$lMap[$x] =  $temp;
                        }
                        $temp="";
                    }
                }
                $var = getNextVar();
            }
                $lMap[$x]= "_";
        }
        else if(strncmp(trim($statements[$x]), "for", 3)==0)
        {
            $newStr=substr($statements[$x],4,-2);
            $newStr=strtok($newStr, ";");
            $newStr = getNextVar2();
            $newStr .= ",";
            $temp= "";
            for($y = 0; $y < strlen($newStr); $y++)
            {
                if(ctype_alpha($newStr[$y]))
                {
                    $temp .=$newStr[$y];
                }
                else
                {
                    if($temp!="")
                    {
                        $rmap[$x][] = $temp;
                        $lmap[$x][] = $temp;
                        $lMap[$x] = "+";
                    }
                     
                    $temp="";
                }
                 
             
            }
            if(empty($rmap[$x])){
                    $rmap[$x][] = "-";
                    $lmap[$x][] = "-";
                    $lMap[$x]="+";
            }
             
        }
        else if(strncmp(trim($statements[$x]), "if", 2)==0)
        {
             
            $newStr=substr($statements[$x],3,-2);
             
            $newStr .= ",";
            $temp= "";
            for($y = 0; $y < strlen($newStr); $y++)
            {
                if(ctype_alpha($newStr[$y]))
                {
                    $temp .=$newStr[$y];
                }
                else
                {
                     
                    if($temp!="")
                    {
                         
                        $rmap[$x][] = $temp;
                        $lmap[$x][] = $temp;
                        $lMap[$x]= "+";
                    }
                     
                    $temp="";
                     
                }
                 
             
            }
            if(empty($rmap[$x])){
                    $rmap[$x][] = "-";
                    $lmap[$x][] = "-";
                    $lMap[$x] = "+";
            }
        }
        else if(strncmp(trim($statements[$x]), "while", 5)==0)
        {
             
            $newStr=substr($statements[$x],5,-2);
             
            $newStr .= ",";
            $temp= "";
            for($y = 0; $y < strlen($newStr); $y++)
            {
                if(ctype_alpha($newStr[$y]))
                {
                    $temp .=$newStr[$y];
                }
                else
                {
                     
                    if($temp!="")
                    {
                         
                        $rmap[$x][] = $temp;
                        $lmap[$x][] = $temp;
                        $lMap[$x]="+";
                    }
                     
                    $temp="";
                     
                }
                 
             
            }
            if(empty($rmap[$x])){
                    $rmap[$x][] = "-";
                    $lmap[$x][] = "-";
                    $lMap[$x] = "+";
            }
        }
        else if(strncmp(trim($statements[$x]), "else", 4)==0)
        {
            $rmap[$x][] = "-";
            $lMap[$x] = "+";
        }
        else if(strncmp(trim($statements[$x]), "}while", 6)==0)
        {
             
            $newStr=substr($statements[$x],6,-2);
            $newStr .= ",";
            $temp= "";
            for($y = 0; $y < strlen($newStr); $y++)
            {
                if(ctype_alpha($newStr[$y]))
                {
                    $temp .=$newStr[$y];
                }
                else
                {
                    if($temp!="")
                    {
                        $rmap[$x][] = $temp;
                        $lmap[$x][] = $temp;
                        $lMap[$x] = "+";
                    }
                    $temp="";
                }
                 
            }
            if(empty($rmap[$x])){
                    $rmap[$x][] = "-";
                    $lmap[$x][] = "-";
                    $lMap[$x] = "+";
            }
        }
        else if(strstr($statements[$x],"do") || strstr($statements[$x],"{") || strstr($statements[$x],"}") ){
                $lmap[$x][] = "-";
                $rmap[$x][] = "-";
                $lMap[$x] = "-";
                 
        }
        // for parsing done
        else if((($statements[$x][$len-3]=="+")&&($statements[$x][$len-4]=="+"))||(($statements[$x][$len-3]=="-")&&($statements[$x][$len-4]=="-")))
        {
            $newStr=substr($statements[$x],0,-4);
            $lmap[$x][] = $newStr;
            $rmap[$x][] = $newStr;
            $lMap[$x] = $newStr;
             
        }
        else{
            $cFlag=0;
                for($j = 0; $j < strlen($statements[$x]); $j++){
                      if($statements[$x][$j] == "+" || $statements[$x][$j] == "-" || $statements[$x][$j] == "*" || $statements[$x][$j] == "%" || $statements[$x][$j] == "/" || $statements[$x][$j] == "=" ){
                        if($temp != ""){
                            if($statements[$x][$j] == "=") {
                                $lmap[$x][] = $temp;
                                $lMap[$x] = $temp;
                                $flag = 1;
                            }
                            else if( ($j+1 < strlen($statements[$x])) && $statements[$x][$j+1] == "="){
                                 
                                $lmap[$x][] = $temp;
                                $lMap[$x] = $temp;
                                $rmap[$x][] = $temp;
                                $j++;
                                $flag = 1;
                            }
                            else if($flag == 1){
                                if($temp != "")
                                    $rmap[$x][] = $temp;
                            }
                        }
                        $temp = "";
                    }
                    else{
                        if(ctype_alpha($statements[$x][$j]))
                            $temp .= $statements[$x][$j];
                    }
                }
                if(ctype_alpha($temp)){
                    $rmap[$x][] = $temp;
                }
                if($flag == 0 && !empty($rmap[$x]) ){
                    for($z = 0; $z < count($rmap[$x]); $z++)
                    {
                        $lmap[$x][] = $rmap[$x][$z];
                        $lMap[$x] = $rmap[$x][$z];
                    }
                }
                if(empty($rmap[$x])){
                    $rmap[$x][] = "-";                 
                }          
        }
    }
    /*for($i = 0; $i<count($lMap); $i++)
    {
        //echo " <br>#".$i." ";
        //echo $lMap[$i];
        //echo " =>  "; 
            //for($x = 0; $x<count($rmap[$i]); $x++){
        for($y = 0; $y<count($rmap[$i]); $y++){
            //echo $rmap[$i][$y].", ";
        }
            //}
         
    }*/
    for($i = 0; $i<count($lMap); $i++)
    {
        //echo " <br>#".$i." ";
        //echo $lMap[$i];
        //echo " =>  "; 
        for($y = 0; $y<count($rmap[$i]); $y++){
            //echo $rmap[$i][$y].", ";
        }
             
         
    }
    $dFlag=0;
    $fFlag=0;
    $itr=0;
    $count1=0;
    $xFlag=0;
    if($qFlag==1)
    {
        //$lCcriteria;
        //echo "here<br>";
        ////echo $start." ".$end." ".$tStart." ".$tEnd."<br>";
        for($x=$printStmt-1;$x>=0;$x--)
        {
            if(strncmp(trim($statements[$x]), "}", 1)==0)
            {
                if($end==$x)
                {
                    //echo $x;
                    $lCriteria=$criteria;
                }
                else if($tEnd==$x)
                {
                    //echo $x;
                    $lCriteriA=$lCriteria;
                }
                 
                 
                //$lCriteria=$criteria;
            }
            if($itr==0)
            {
                if((strncmp(trim($statements[$x]), "{", 1)==0)&&(strncmp(trim($statements[$x-1]), "else", 4)!=0))
                {
                    if($start==$x)
                    {                  
                        for($y=0;$y<count($lCriteria);$y++)
                        {  
                            $eFlag=0;
                            for($z=0;$z<count($criteria);$z++)
                            {
                                if($criteria[$z]==$lCriteria[$y])
                                {
                                    $eFlag=1;
                                }
                            }
                            if($eFlag==0)
                            {
                             
                                array_push($criteria,$lCriteria[$y]);
                                array_push($init,$lCriteria[$y]);                               // problem if alreay present
                            }
                        }
                    }
                    else if($tStart==$x)
                    {
                        for($y=0;$y<count($lCriteriA);$y++)
                        {  
                            $eFlag=0;
                            for($z=0;$z<count($lCriteria);$z++)
                            {
                                if($lCriteria[$z]==$lCriteriA[$y])
                                {
                                    $eFlag=1;
                                }
                            }
                            if($eFlag==0)
                            {
                             
                                array_push($lCriteria,$lCriteriA[$y]);
                                array_push($init,$lCriteria[$y]);   // problem if alreay present
                            }
                        }
                    }
                }/*
                else if((strncmp(trim($statements[$x]), "{", 1)==0)&&(strncmp(trim($statements[$x-1]), "else", 4)==0))
                {
                    if($dFlag==1)
                    {
                        if($itr==0)
                        {
                            $fFlag=1;
                            for($y=0;$y<count($rmap[$x]);$y++)
                            {
                                array_push($eCriteria,$lCriteria[$y]);
                                //array_push($lCriteria,$rmap[$x][$y]);
                            }
                            array_push($slice,$x-1);
                        }
                    //$x=$endArr[$x+1];
                    //$itr=1;
                    }
                    $dFlag=0;// doubt
 
                }*/
            }
            if($lMap[$x]=="_")
            {
                //echo "int ";
                $eFlag=0;
                for($y=0;$y<count($rmap[$x]);$y++)
                {
                     
                    for($z=0;$z<count($init);$z++)
                    {
                        //echo $criteria[$z]." ";
                        if($init[$z]==$rmap[$x][$y])
                        {
                            $eFlag=1;
                        }
                    }
                }
                if(($eFlag==1)&&(strncmp(trim($statements[$x]), "scanf", 5)!=0))
                {
                     
                    array_push($slice,$x); 
                        //array_push($criteria,$rmap[$x][$y]);
                }
                 
                if((strncmp(trim($statements[$x]), "scanf", 5)==0))
                {
                    $eFlag=0;
                    for($y=0;$y<count($rmap[$x]);$y++)
                    {
                         
                        for($z=0;$z<count($criteria);$z++)
                        {
                            //echo $criteria[$z]." ";
                            if($criteria[$z]==$rmap[$x][$y])
                            {
                                $eFlag=1;
                            }
                        }
                    }
                    if($eFlag==1)
                    {
                        array_push($slice,$x); 
                    }
                        //array_push($criteria,$rmap[$x][$y]);
                }
                if(strncmp(trim($statements[$x]), "scanf", 5)==0)
                {
                    //echo "scan";
                    for($w=0;$w<count($rmap[$x]);$w++)
                    {
                        foreach (array_keys($criteria,$rmap[$x][$w]) as $key)
                        {
                            unset($criteria[$key]);
                            $criteria=array_values($criteria);
                     
                        }
                    }
                }
                 
            }
            else if((strncmp(trim($statements[$x]), "while", 5)==0)||(strncmp(trim($statements[$x]), "for", 3)==0))
            {
                if($dFlag==1)
                {
                    if($startArr[$x]==$endArr[$x])
                    {
                        if($itr==0)
                        {
                            for($y=0;$y<count($rmap[$x]);$y++)
                            {
                                $eFlag=0;
                                for($z=0;$z<count($criteria);$z++)
                                {
                                    if($criteria[$z]==$rmap[$x][$y])
                                    {
                                        $eFlag=1;
                                    }
                                }  
                                if($eFlag==0)
                                {
                                 
                                    array_push($criteria,$rmap[$x][$y]);
                                    array_push($init,$rmap[$x][$y]);
                                }
                                $eFlag=0;
                                for($z=0;$z<count($lCriteria);$z++)
                                {
                                    if($lCriteria[$z]==$rmap[$x][$y])
                                    {
                                        $eFlag=1;
                                    }
                                }  
                                if($eFlag==0)
                                {
                                 
                                    array_push($lCriteria,$rmap[$x][$y]);
                                    array_push($init,$rmap[$x][$y]);
                                }
                            }
                            array_push($slice,$x);
                        }
                        $x=$endArr[$x+1];
                        $itr=1;
                        $dFlag=0;
                    }
                    else
                    {
                        if($itr==0)
                        {
                            for($y=0;$y<count($rmap[$x]);$y++)
                            {
                                $eFlag=0;
                                for($z=0;$z<count($lCriteria);$z++)
                                {
                                    if($lCriteria[$z]==$rmap[$x][$y])
                                    {
                                        $eFlag=1;
                                    }
                                }  
                                if($eFlag==0)
                                {
                                 
                                    array_push($lCriteria,$rmap[$x][$y]);
                                    array_push($init,$rmap[$x][$y]);
                                }
                                $eFlag=0;
                                for($z=0;$z<count($lCriteriA);$z++)
                                {
                                    if($lCriteriA[$z]==$rmap[$x][$y])
                                    {
                                        $eFlag=1;
                                    }
                                }  
                                if($eFlag==0)
                                {
                                 
                                    array_push($lCriteriA,$rmap[$x][$y]);
                                    array_push($init,$rmap[$x][$y]);
                                }
                            }
                            array_push($slice,$x);
                        }
                        $x=$endArr[$x+1];
                        $itr=1;
                    }
                }
                else
                {
                    $itr=0;
                }
                 
                 
            }
            else if(strncmp(trim($statements[$x]), "if", 2)==0)
            {
                if($dFlag==1)
                {
                    if($startArr[$x]==$endArr[$x])
                    {
                        if($itr==0)
                        {
                            for($y=0;$y<count($rmap[$x]);$y++)
                            {
                                $eFlag=0;
                                for($z=0;$z<count($criteria);$z++)
                                {
                                    if($criteria[$z]==$rmap[$x][$y])
                                    {
                                        $eFlag=1;
                                    }
                                }  
                                if($eFlag==0)
                                {
                                 
                                    array_push($criteria,$rmap[$x][$y]);
                                    array_push($init,$rmap[$x][$y]);
                                }
                                //  array_push($criteria,$rmap[$x][$y]);
                                    //array_push($lCriteria,$rmap[$x][$y]);
                            }
                            array_push($slice,$x);
                        }
                        $dFlag=0;
                    }
                    else
                    {
                        //if($itr==0)
                        //{
                            for($y=0;$y<count($rmap[$x]);$y++)
                            {
                                $eFlag=0;
                                for($z=0;$z<count($lCriteria);$z++)
                                {
                                    if($lCriteria[$z]==$rmap[$x][$y])
                                    {
                                        $eFlag=1;
                                    }
                                }  
                                if($eFlag==0)
                                {
                                 
                                    array_push($lCriteria,$rmap[$x][$y]);
                                    array_push($init,$rmap[$x][$y]);
                                }
                                //  array_push($criteria,$rmap[$x][$y]);
                                    //array_push($lCriteria,$rmap[$x][$y]);
                            }
                            if($mFlag==1)
                            {
                                array_push($slice,$x);//this is creating problem
                                $mFlag=0;
                            }
                        //}
                    }
                    //$x=$endArr[$x+1];
                    //$itr=1;
                }
                if($fFlag==1)
                {
                    if($startArr[$x]==$endArr[$x])
                    {
                        for($y=0;$y<count($eCriteria);$y++)
                        {
                            $eFlag=0;
                                for($z=0;$z<count($criteria);$z++)
                                {
                                    if($criteria[$z]==$eCriteria[$y])
                                    {
                                        $eFlag=1;
                                    }
                                }  
                                if($eFlag==0)
                                {
                                 
                                    array_push($criteria,$eCriteria[$y]);
                                    array_push($init,$eCriteria[$y]);
                                }
                                //array_push($criteria,$eCriteria[$y]);
                                //array_push($lCriteria,$rmap[$x][$y]);
                        }
                    }
                    else
                    {
                        for($y=0;$y<count($eCriteria);$y++)
                        {
                            $eFlag=0;
                                for($z=0;$z<count($lCriteria);$z++)
                                {
                                    if($lCiteria[$z]==$eCriteria[$y])
                                    {
                                        $eFlag=1;
                                    }
                                }  
                                if($eFlag==0)
                                {
                                 
                                    array_push($lCriteria,$eCriteria[$y]);
                                    array_push($init,$eCriteria[$y]);
                                }
                                //array_push($criteria,$eCriteria[$y]);
                                //array_push($lCriteria,$rmap[$x][$y]);
                        }
                    }
                }
                 // doubt
            }
            if(count($criteria)>0)
            {
                 
                    if($lMap[$x]=='-')
                    {
                        ////echo  $lMap[$x]." ".$statements[$x]."not slice<br>";
                    }
                    else
                    {
                         
                        $cFlag=0;
                        if(($startArr[$x]==$endArr[$x])&&(strncmp(trim($statements[$x]), "while", 5)!=0)&&(strncmp(trim($statements[$x]), "for", 3)!=0)&&(strncmp(trim($statements[$x]), "if", 2)!=0))
                        {
                            for($y=0;$y<count($criteria);$y++) /// might give problem
                            {
                                if($lMap[$x]==$criteria[$y])
                                {
                                    $cFlag=1;
                                }
                            }
                            if($cFlag==1)
                            {
                                array_push($slice,$x);
                                ////echo  $statements[$x]." ".$x."<br>";
                                $visited[$x]=1;
                                foreach (array_keys($criteria,$lMap[$x]) as $key)
                                {
                                    ////echo "<br>key ".$criteria[$key];
                                    unset($criteria[$key]);
                                    $criteria=array_values($criteria);
                                }
                                for($z=0;$z<count($rmap[$x]);$z++)
                                {
                                    array_push($criteria,$rmap[$x][$z]);
                                    array_push($init,$rmap[$x][$z]);
                                }
                            }
                            else
                            {
                                ////echo $statements[$x]." not slice ";// not a problem we need not to add this one
                            }
                        }
                        else
                        {
                            if($startArr[$x]==$start)
                            {   for($y=0;$y<count($lCriteria);$y++) /// might give problem
                                {
                                    if($lMap[$x]==$lCriteria[$y])
                                    {
                                         
                                        if($visited[$x]==0)
                                        {
                                            $cFlag=1;
                                            $dFlag=1;
                                        }
                                    }
                                }
                                 
                                if($cFlag==1)
                                {
                                    array_push($slice,$x);
                                    ////echo  $statements[$x]." ".$x."<br>";
                                    $visited[$x]=1;
                                    foreach (array_keys($lCriteria,$lMap[$x]) as $key)
                                    {
                                        unset($lCriteria[$key]);
                                        $lCriteria=array_values($lCriteria);
                                    }
                                    for($z=0;$z<count($rmap[$x]);$z++)
                                    {
                                        array_push($lCriteria,$rmap[$x][$z]);
                                        array_push($init,$rmap[$x][$z]);
                                    }
                                     
                                 
                                }
                                else
                                {
                                    ////echo $statements[$x]." not slice ";
                                }
                            }
                            else
                            {
                                for($y=0;$y<count($lCriteriA);$y++) /// might give problem
                                {
                                    if($lMap[$x]==$lCriteriA[$y])
                                    {
                                         
                                        if($visited[$x]==0)
                                        {
                                            $cFlag=1;
                                            $dFlag=1;
                                            $mFlag=1;
                                        }
                                    }
                                }
                                 
                                if($cFlag==1)
                                {
                                    array_push($slice,$x);
                                    ////echo  $statements[$x]." ".$x."<br>";
                                    $visited[$x]=1;
                                    foreach (array_keys($lCriteriA,$lMap[$x]) as $key)
                                    {
                                        unset($lCriteriA[$key]);
                                        $lCriteriA=array_values($lCriteria);
                                    }
                                    for($z=0;$z<count($rmap[$x]);$z++)
                                    {
                                        array_push($lCriteriA,$rmap[$x][$z]);
                                        array_push($init,$rmap[$x][$z]);
                                    }
                                     
                                 
                                }
                                else
                                {
                                    ////echo $statements[$x]." not slice ";
                                }
                            }
                        }
                         
                    }
                 
            }
        //for end  
        }
    }
    else
    {
        for($x=$printStmt-1;$x>=0;$x--)
        {
            $count1++;
            if(strncmp(trim($statements[$x]), "}", 1)==0)
            {
                $lCriteria=$criteria;
            }
            if($itr==0)
            {
                if((strncmp(trim($statements[$x]), "{", 1)==0)&&(strncmp(trim($statements[$x-1]), "else", 4)!=0))
                {
                    for($y=0;$y<count($lCriteria);$y++)
                    {  
                        $eFlag=0;
                        for($z=0;$z<count($criteria);$z++)
                        {
                            if($criteria[$z]==$lCriteria[$y])
                            {
                                $eFlag=1;
                            }
                        }
                        if($eFlag==0)
                        {
                         
                            array_push($criteria,$lCriteria[$y]);
                            array_push($init,$lCriteria[$y]);                           // problem if alreay present
                        }
                    }
                }
                else if((strncmp(trim($statements[$x]), "{", 1)==0)&&(strncmp(trim($statements[$x-1]), "else", 4)==0))
                {
                    if($dFlag==1)
                    {
                        if($itr==0)
                        {
                            $fFlag=1;
                            for($y=0;$y<count($rmap[$x]);$y++)
                            {
                                array_push($eCriteria,$lCriteria[$y]);
                                array_push($init,$lCriteria[$y]);
                                //array_push($lCriteria,$rmap[$x][$y]);
                            }
                            array_push($slice,$x-1);
                        }
                    //$x=$endArr[$x+1];
                    //$itr=1;
                    }
                    /*else
                    {
                        $itr=0;
                    }*/
                    $dFlag=0;// doubt
 
                }
            }
            if($lMap[$x]=="_")
            {
                //echo "int ";
                $eFlag=0;
                for($y=0;$y<count($rmap[$x]);$y++)
                {
                     
                    for($z=0;$z<count($init);$z++)
                    {
                        //echo $criteria[$z]." ";
                        if($init[$z]==$rmap[$x][$y])
                        {
                            $eFlag=1;
                        }
                    }
                }
                if(($eFlag==1)&&(strncmp(trim($statements[$x]), "scanf", 5)!=0))
                {
                     
                    array_push($slice,$x); 
                        //array_push($criteria,$rmap[$x][$y]);
                }
                 
                if((strncmp(trim($statements[$x]), "scanf", 5)==0))
                {
                    $eFlag=0;
                    for($y=0;$y<count($rmap[$x]);$y++)
                    {
                         
                        for($z=0;$z<count($criteria);$z++)
                        {
                            //echo $criteria[$z]." ";
                            if($criteria[$z]==$rmap[$x][$y])
                            {
                                $eFlag=1;
                            }
                        }
                    }
                    if($eFlag==1)
                    {
                        array_push($slice,$x); 
                    }
                        //array_push($criteria,$rmap[$x][$y]);
                }
                if(strncmp(trim($statements[$x]), "scanf", 5)==0)
                {
                    //echo "scan";
                    for($w=0;$w<count($rmap[$x]);$w++)
                    {
                        foreach (array_keys($criteria,$rmap[$x][$w]) as $key)
                        {
                            unset($criteria[$key]);
                            $criteria=array_values($criteria);
                     
                        }
                    }
                }
                 
            }
            else if((strncmp(trim($statements[$x]), "while", 5)==0)||(strncmp(trim($statements[$x]), "for", 3)==0))
            {
                if($dFlag==1)
                {
                    if($itr==0)
                    {
                        for($y=0;$y<count($rmap[$x]);$y++)
                        {
                            $eFlag=0;
                            for($z=0;$z<count($criteria);$z++)
                            {
                                if($criteria[$z]==$rmap[$x][$y])
                                {
                                    $eFlag=1;
                                }
                            }  
                            if($eFlag==0)
                            {
                             
                                array_push($criteria,$rmap[$x][$y]);
                                array_push($init,$rmap[$x][$y]);
                            }
                            $eFlag=0;
                            for($z=0;$z<count($lCriteria);$z++)
                            {
                                if($lCriteria[$z]==$rmap[$x][$y])
                                {
                                    $eFlag=1;
                                }
                            }  
                            if($eFlag==0)
                            {
                             
                                array_push($lCriteria,$rmap[$x][$y]);
                                array_push($init,$rmap[$x][$y]);
                            }
                        }
                        array_push($slice,$x);
                    }
                    $x=$endArr[$x+1];
                    $itr=1;
                }
                else
                {
                    $itr=0;
                }
                $dFlag=0;
                 
            }
            else if(strncmp(trim($statements[$x]), "do", 2)==0)
            {
                if($dFlag==1)
                {
                    if($itr==0)
                    {
                        //$temp=$endArr[$x+1];
                        for($y=0;$y<count($rmap[$x]);$y++)
                        {
                            $eFlag=0;
                            for($z=0;$z<count($criteria);$z++)
                            {
                                if($criteria[$z]==$rmap[$temp][$y])
                                {
                                    $eFlag=1;
                                }
                            }  
                            if($eFlag==0)
                            {  
                                array_push($criteria,$rmap[$temp][$y]);
                                array_push($init,$rmap[$temp][$y]);
                            }
                            $eFlag=0;
                            for($z=0;$z<count($lCriteria);$z++)
                            {
                                if($lCriteria[$z]==$rmap[$temp][$y])
                                {
                                    $eFlag=1;
                                }
                            }  
                            if($eFlag==0)
                            {  
                                array_push($lCriteria,$rmap[$temp][$y]);
                                array_push($init,$rmap[$temp][$y]);
                            }  
                        }
                        array_push($slice,$x);
                        array_push($slice,$temp);
                    }
                    $x=$endArr[$x+1];
                    $itr=1;
                }
                else
                {
                    $itr=0;
                }
                $dFlag=0;
            }
            else if(strncmp(trim($statements[$x]), "if", 2)==0)
            {
                if($dFlag==1)
                {
                    if($itr==0)
                    {
                        for($y=0;$y<count($rmap[$x]);$y++)
                        {
                            $eFlag=0;
                            for($z=0;$z<count($criteria);$z++)
                            {
                                if($criteria[$z]==$rmap[$x][$y])
                                {
                                    $eFlag=1;
                                }
                            }  
                            if($eFlag==0)
                            {
                             
                                array_push($criteria,$rmap[$x][$y]);
                                array_push($init,$rmap[$x][$y]);
                            }
                            //  array_push($criteria,$rmap[$x][$y]);
                                //array_push($lCriteria,$rmap[$x][$y]);
                        }
                        array_push($slice,$x);
                    }
                    //$x=$endArr[$x+1];
                    //$itr=1;
                }
                if($fFlag==1)
                {
                    for($y=0;$y<count($eCriteria);$y++)
                    {
                        $eFlag=0;
                            for($z=0;$z<count($criteria);$z++)
                            {
                                if($criteria[$z]==$eCriteria[$y])
                                {
                                    $eFlag=1;
                                }
                            }  
                            if($eFlag==0)
                            {
                             
                                array_push($criteria,$eCriteria[$y]);
                                array_push($init,$eCriteria[$y]);
                            }
                            //array_push($criteria,$eCriteria[$y]);
                            //array_push($lCriteria,$rmap[$x][$y]);
                    }
                }
                $dFlag=0; // doubt
            }
            if(count($criteria)>0)
            {
                if($lMap[$x]=='-')
                {
                    ////echo  $lMap[$x]." ".$statements[$x]."not slice<br>";
                }
                else
                {
                     
                    $cFlag=0;
                    if($startArr[$x]==$endArr[$x])
                    {
                        for($y=0;$y<count($criteria);$y++) /// might give problem
                        {
                            if($lMap[$x]==$criteria[$y])
                            {
                                $cFlag=1;
                            }
                        }
                        if($cFlag==1)
                        {
                            array_push($slice,$x);
                            ////echo  $statements[$x]." ".$x."<br>";
                            $visited[$x]=1;
                            foreach (array_keys($criteria,$lMap[$x]) as $key)
                            {
                                ////echo "<br>key ".$criteria[$key];
                                unset($criteria[$key]);
                                $criteria=array_values($criteria);
                            }
                            for($z=0;$z<count($rmap[$x]);$z++)
                            {
                                array_push($criteria,$rmap[$x][$z]);
                                array_push($init,$rmap[$x][$z]);
                            }
                        }
                        else
                        {
                            ////echo $statements[$x]." not slice ";// not a problem we need not to add this one
                        }
                    }
                    else
                    {
                        for($y=0;$y<count($lCriteria);$y++) /// might give problem
                        {
                            if($lMap[$x]==$lCriteria[$y])
                            {
                                 
                                if($visited[$x]==0)
                                {
                                    $cFlag=1;
                                    $dFlag=1;
                                }
                            }
                        }
                         
                        if($cFlag==1)
                        {
                            array_push($slice,$x);
                            ////echo  $statements[$x]." ".$x."<br>";
                            $visited[$x]=1;
                            foreach (array_keys($lCriteria,$lMap[$x]) as $key)
                            {
                                unset($lCriteria[$key]);
                                $lCriteria=array_values($lCriteria);
                            }
                            for($z=0;$z<count($rmap[$x]);$z++)
                            {
                                array_push($lCriteria,$rmap[$x][$z]);
                                array_push($init,$rmap[$x][$z]);
                            }
                             
                         
                        }
                        else
                        {
                            ////echo $statements[$x]." not slice ";
                        }
                    }
                     
                }
            }
            /*if($startArr[$x]==$endArr[$x])
            {
                for($y=0;$y<count($criteria);$y++) /// might give problem
                {
                    //echo " ".$criteria[$y];
                }
            }
            else
            {
                for($y=0;$y<count($lCriteria);$y++) /// might give problem
                {
                    //echo " ".$lCriteria[$y];
                }
            */
        }
    }
    ////echo "hello";
    for($y=0;$y<count($init);$y++)
    {
        //$marked[$slice[$y]]=1;
        //echo $init[$y]." ";
    }
    for($y=0;$y<count($slice);$y++)
    {
        $marked[$slice[$y]]=1;
    }
     
}
function getNextLine()
{
    return strtok("\n");
}
function getNextVar()
{
    return strtok(",");
}
function getNextVar2()
{
    return strtok(";");
}  
?>
</body>
</html>
