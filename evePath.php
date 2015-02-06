<?php

/*
 * The MIT License
 *
 * Copyright 2015 Lars Naurath.
 *
 * Based upon: https://github.com/fuzzysteve/Eve-Route-Plan
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */


/**
 * Description of evePath
 *
 * @author Lars Naurath
 */
class evePath {
    private $jumpArray = array();
    private $securityArray = array();
    
    public function __construct(){
        if(class_exists('Memcache')){
            $memcache = new Memcache;
            $memcache->connect('localhost', 11211) or die ("Could not connect");
            if($memcache->get('evePath.jumpArray') === FALSE || $memcache->get('evePath.securityArray') === FALSE){
                $this->loadData();
                $memcache->set('evePath.jumpArray', $this->jumpArray);
                $memcache->set('evePath.securityArray', $this->securityArray);
            }
            else{
                $this->jumpArray = $memcache->get('evePath.jumpArray');
                $this->securityArray = $memcache->get('evePath.securityArray');
            }
                
        }
        else{
            $this->loadData();
        }
        
        
    }
    
    private function loadData(){
        $data = json_decode(file_get_contents(dirname(__FILE__) . "/data/data.json"), true);
        $this->jumpArray = $data['jumps'];
        $this->securityArray = $data['security'];
    }
    
    public function getPath($fromSystemID, $toSystemID, $minSecurity = 0.5){
        $jumpNum = 1;
        $route = array();
        foreach($this->jumpArray[$fromSystemID] as $systemID) {
            if ($systemID == $toSystemID) {
                    $jumpNum = 2;
                    $route[] = $toSystemID;
                    break;
            }
        }
        if ($jumpNum == 1) {
            foreach($this->graph_find_path($fromSystemID, $toSystemID, $minSecurity) as $n) {
                if ($jumpNum > 1) {
                    $route[]=  $n;
                }
                $jumpNum++;
            }
        }
        return $route;
    }
    
    private function graph_find_path($fromSystemID, $toSystemID, $minSecurity, $M = 50000){
	$P = array(); // $P will hold the result path at the end.
        $V = array(); // For each Node ID create a "visit information"$R = array(trim($fromSystemID)); // We are going to keep a list of nodes that are "within reach"
	$R = array(trim($fromSystemID));// We are going to keep a list of nodes that are "within reach",

	$fromSystemID = trim($fromSystemID);
	$toSystemID = trim($toSystemID);

	while (count($R) > 0 && $M > 0){
            $M--;
            $X = trim(array_shift($R));

            foreach($this->jumpArray[$X] as $Y){
                if($this->securityArray[$Y] >= $minSecurity){
                    $Y = trim($Y);
                    // See if we got a solution
                    if ($Y == $toSystemID){
                        // We did? Construct a result path then
                        array_push($P, $toSystemID);
                        array_push($P, $X);
                        while ($V[$X] != $fromSystemID){
                            array_push($P, trim($V[$X]));
                            $X = $V[$X];
                        }
                        array_push($P, $fromSystemID);
                        return array_reverse($P);
                    }
                    // First time we visit this node?
                    if (!array_key_exists($Y, $V)){
                        // Store the path so we can track it back,
                        $V[$Y] = $X;
                        // and add it to the "within reach" list
                        array_push($R, $Y);
                    }
                }
            }
	}

	return $P;
    }
    
    
}
