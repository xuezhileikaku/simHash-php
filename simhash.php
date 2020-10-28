<?php


class SimhashClass
{
//    private $Token;
    private $token;

    /**
     * Simhash constructor.
     * @param $doc
     * The document we want to generate the Simhash value
     * @param int $fp_len
     * The number of bits we want our hash to be consisted of.
     * Since we are hashing each token of the document using md5 (which produces a 128 bit hash value) then this variable fp_len should be 128. Feel free to change this value if you use a different hash function for your tokens.
     * return The Simhash value of a document ex. '0000100001110'
     */
    public function simhash($doc, $fp_len = 128)
    {
        $this->token = $this->tokenize($doc);
//        var_dump($this->token);
        $token_dict = $this->calcWeights($this->token, $fp_len);
//        var_dump($token_dict);

        $fp_hash_list = $this->hashThreshold($token_dict, $fp_len);
//        var_dump($fp_hash_list);
//        exit();
        return $fp_hash_list;
    }

    private function tokenize($doc)
    {
        $str = strtolower($doc);
        $search = [',', ';', '?', ':', '.', '   ', '  '];
        $str = str_replace($search, ' ', $str);
        $arr = explode(' ', $str);
//        doc = filter(None, doc)
        return $arr;
    }

    private function str_baseconvert($str, $frombase = 10, $tobase = 36)
    {
        $str = trim($str);
        if (intval($frombase) != 10) {
            $len = strlen($str);
            $q = 0;
            for ($i = 0; $i < $len; $i++) {
                $r = base_convert($str[$i], $frombase, 10);
                $q = bcadd(bcmul($q, $frombase), $r);
            }
        } else $q = $str;

        if (intval($tobase) != 10) {
            $s = '';
            while (bccomp($q, '0', 0) > 0) {
                $r = intval(bcmod($q, $tobase));
                $s = base_convert($r, 10, $tobase) . $s;
                $q = bcdiv($q, $tobase, 0);
            }
        } else $s = $q;

        return $s;
    }

    private function md5Hash($token, $fp_len)
    {
        $str_md5 = md5($token);
//        var_dump($str_md5);
//        var_dump($str_int);
        $code = $this->str_baseconvert($str_md5, 16, 2);
//        var_dump($code);
//        exit();
        return str_pad($code, $fp_len, "0", STR_PAD_LEFT);
    }

    private function binconv($fp)
    {
        $vec = [];

        for ($i = 0; $i < strlen($fp); $i++) {
            if ($fp[$i] == '0') {
                $vec[$i] = -1;
            } else {
                $vec[$i] = 1;
            }
        }
        return $vec;
    }

    private function calcWeights($terms, $fp_len)
    {
        $term_dict = [];
        foreach ($terms as $term) {
            if ($term !== '') {
//                var_dump($term);
//                exit();
                if (!isset($term_dict[$term])) {

                    $fp_hash = $this->md5Hash($term, $fp_len);
//                    var_dump($fp_hash);
//                    var_dump($term);
//                    exit();
                    $term_dict[$term]['hashList'] = $this->binconv($fp_hash);
//                    var_dump($fp_hash_list);
//                $token = $this->setToken($fp_hash_list, 0);
                    $term_dict[$term]['weight'] = 0;
//                    var_dump($term_dict);
//                    exit();
                }
                $term_dict[$term]['weight'] += 1;
            }

        }
        return $term_dict;
    }

    private function hashThreshold($token_dict, $fp_len)
    {
//        $sum_hash = array_fill(0, $fp_len, 0);
        $sum_hash = array_fill(0, $fp_len, 0);
//        var_dump($sum_hash);
        foreach ($token_dict as $word => $v) {
//            var_dump($v);

            foreach ($v['hashList'] as $i => $k) {
//                var_dump($sum_hash[$word][$i]);
//                var_dump($k * $v['weight']);
                $sum_hash[$i] += $k * $v['weight'];
//                var_dump($sum_hash);
//                exit();
            }
//            var_dump($sum_hash);
//            exit();
        }
//        var_dump($sum_hash);
//        exit();
        foreach ($sum_hash as $i => $s) {
            if ($s > 0) {
                $sum_hash[$i] = 1;
            } else {
                $sum_hash[$i] = 0;
            }
        }

        return implode('', $sum_hash);
    }

    public function hamming_distance($a, $b)
    {
        $a1 = str_split($a);
        $a2 = str_split($b);
        $dh = 0;
        for ($i = 0; $i < count($a1); $i++)
            if ($a1[$i] != $a2[$i]) $dh++;
        return $dh;
    }

}

$doc = "Which of the following, if true, would best support the c";
//If the sides of R are in the ratio 2 : 3,The perimeters of square region S and rectangular region R are equal, what is the ratio of the area of region R to the area of region S ?';
$sim = new SimhashClass();
$re = $sim->simhash($doc);
$doc1 = "Which of the following, if true, would best support the o";
//The perimeters of square region S and rectangular region R are equal,If the sides of R are in the ratio 2 : 3, what is the ratio of the area of region R to the area of region S ?';
$re1 = $sim->simhash($doc1);

$dist = $sim->hamming_distance($re, $re1);
var_dump($re);
var_dump($re1);
var_dump($dist);
exit();
