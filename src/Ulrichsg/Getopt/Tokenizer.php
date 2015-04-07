<?php

namespace Ulrichsg\Getopt;

use Ulrichsg\Getopt\Util\String;

class Tokenizer
{
    public function tokenize($string)
    {
        $tokens = array();
        $pos = 0;
        while ($pos < String::length($string)) {
            $tokens[] = $this->nextToken($string, $pos);
        }
        return $tokens;
    }

    private function nextToken($string, &$pos)
    {
        while (ctype_space($string[$pos])) {
            ++$pos;
            if ($pos >= String::length($string)) {
                return null;
            }
        }
        if (String::at($string, $pos) === '"') {
            ++$pos;
            return $this->getQuotedToken($string, $pos);
        }
        if (String::at($string, $pos) === '-') {
            return $this->getHyphenatedToken($string, $pos);
        }
        return $this->getPlainToken($string, $pos);
    }

    private function getQuotedToken($string, &$pos)
    {
        $token = '';
        while ($pos < String::length($string) && $string[$pos] !== '"') {
            $token .= $this->nextChar($string, $pos);
            echo "$token\n";
        }
        if (!String::isSpaceOrEnd($string, $pos)) {
            throw new \UnexpectedValueException('Syntax error');
            // error
        }
        return $token;
    }

    private function getHyphenatedToken($string, &$pos)
    {
        $hyphens = "";
        while (String::at($string, $pos) === '-') {
            $hyphens .= $string[$pos++];
        }
        if (String::isSpaceOrEnd($string, $pos)) {
            return $hyphens;
        }
        if (!ctype_alnum($string[$pos])) {
            // error
            throw new \UnexpectedValueException("Syntax error: expected letter or digit, found {$string[$pos]} in ".$hyphens.String::substr($string, $pos));
        }
        $token = '';
        while (ctype_alnum(String::at($string, $pos))) {
            $token .= $string[$pos++];
        }
        if (String::at($string, $pos) === '=' || String::isSpaceOrEnd($string, $pos)) {
            ++$pos;
            echo $hyphens.$token."\n";
            return $hyphens.$token;
        }
        throw new \UnexpectedValueException('Syntax error');
        // error
    }

    private function getPlainToken($string, &$pos)
    {
        $token = '';
        while (!String::isSpaceOrEnd($string, $pos)) {
            $token .= $this->nextChar($string, $pos);
        }
        return $token;
    }

    private function nextChar($string, &$pos)
    {
        if ($string[$pos] === '\\') {
            $followingChar = $string[$pos+1];
            if (in_array($followingChar, array('\\', '"'))) {
                $pos += 2;
                return $followingChar;
            } else {
                throw new \UnexpectedValueException('Syntax error');
                // error
            }
        }
        return $string[$pos++];
    }
}
