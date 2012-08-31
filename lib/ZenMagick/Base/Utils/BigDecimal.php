<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright 2006-2007 by Dick Munroe, Cottage Software Works, Inc.
 * Copyright (C) 2011-2012 zenmagick.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace ZenMagick\base\utils;

/**
 * Rational number math.
 *
 * @author dickmunroe
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 * @see http://www.phpclasses.org/browse/file/12724.html
 */
class BigDecimal {
    private $n;
    private $d;


    /**
     * Create a new instance.
     *
     * @param mixed n if a numerator/denominator pair is provided or an integer or a floating point number.
     * @param integer d Optional denominator of a rational number; default is <code>null</code>.
     */
    public function __construct($n, $d=null) {
        if (null !== $d) {
            $this->n = $n;
            $this->d = $d;
        } else if (is_object($n) && $n instanceof BigDecimal) {
            $this->n = $n->n;
            $this->d = $n->d;
        } else if (is_numeric($n) && ($n == floor($n))) {
            $this->n = $n;
            $this->d = 1;
        } else if (is_float($n)) {
            $xxx = self::rational($n);
            $this->n = $xxx[0];
            $this->d = $xxx[1];
        } else {
            $this->n = 0;
            $this->d = 1;
        }
    }

    /**
     * Add two rational numbers.
     *
     * @param mixed r The number to be added.
     * @return BigDecimal The result.
     */
    public function add($r) {
        if (!is_object($r)) {
            $r = new BigDecimal($r);
        }

        if ($this->d != $r->d) {
            $n = ($this->n * $r->d) + ($r->n * $this->d);
            $d = ($this->d * $r->d);
        } else {
            $n = $this->n + $r->n;
            $d = $this->d;
        }

        return new BigDecimal($n, $d);
    }

    /**
     * Divide two rational numbers.
     *
     * @param mixed r The number to be divided by.
     * @return BigDecimal The result.
     */
    public function divide($r) {
        // create new instance as we are inverting the value
        $xxx = new BigDecimal($r);
        $xxx->invert();
        return $this->multiply($xxx);
    }

    /**
     * Multiply two rational numbers.
     *
     * @param mixed r The number to be mutplied by.
     * @return BigDecimal The result.
     */
    public function multiply($r) {
        if (!is_object($r)) {
            $r = new BigDecimal($r);
        }

        return new BigDecimal($this->n * $r->n, $this->d * $r->d);
    }

    /**
     * Subtract two rational numbers.
     *
     * @param mixed r The number to be subtracted.
     * @return BigDecimal The result.
     */
    public function subtract($r) {
        if (!is_object($r)) {
            $r = new BigDecimal($r);
        }

        if ($this->d != $r->d) {
            $n = ($this->n * $r->d) - ($r->n * $this->d);
            $d = ($this->d * $r->d);
        } else {
            $n = $this->n - $r->n;
            $d = $this->d;
        }

        return new BigDecimal($n, $d);
    }

    /**
     * Invert the value of this instance.
     *
     * @return BigDecimal This instance.
     */
    public function invert() {
        $xxx = $this->n;
        $this->n = $this->d;
        $this->d = $xxx;
        return $this;
    }

    /**
     * Convert this rational number to the simplest form.
     *
     * @return BigDecimal This instance.
     */
    public function simplify() {
        if ($this->n < $this->d) {
            $factors = self::factor($this->n);
        } else {
            $factors = self::factor($this->d);
        }

        foreach (array_keys($factors) as $factor) {
            if ($factor != 1) {
                do {
                    if (($this->n % $factor == 0) && ($this->d % $factor == 0)) {
                        $this->n = $this->n/$factor;
                        $this->d = $this->d/$factor;
                    } else {
                        break;
                    }
                } while (true);
            }
        }
        return $this;
    }

    /**
     * Get this rational as numerator/denominator pair.
     *
     * @return array [0] = numerator [1] = denominator.
     */
    public function getNDPair() {
        return array($this->n, $this->d);
    }

    /**
     * Get this rational as float.
     *
     * @param int precision The precision (decimal digits); default is <em>2</em>.
     * @return float The float value of this instance.
     */
    public function asFloat($precision=2) {
        return round($this->n/$this->d, $precision);
    }

    /**
     * Return this ratinal as string.
     *
     * @return string The string.
     */
    public function __toString() {
        return "[BigDecimal n=".$this->n.", d=".$this->d."]";
    }

    /*
     * Calculate the rational number version of a floating point number.
     *
     * Note that the rational number returned is NOT guaranteed to be in
     * simplest form, i.e., the algorithm doesn't necessarily find 2/3, but
     * will find 4/6 for it's solution (which is the same as 2/3).
     *
     * A very cool algorithm (and, as near as I can tell, unique).  Basically
     * take a guess, calculate a delta, use the delta to calculate a new
     * denominator, take another guess, keep going until things converge.
     *
     * @param float $x The floating point number to be converted.
     * @param float $theEpsilon How close the fraction should be to x; default is <em>1.0e-06</em>.
     * @return array [0] = numerator [1] = denominator.
     */
    protected static function rational($x, $theEpsilon = 1.0e-06)
    {
        /*
         * Strip off the sign, the algorithm only works on positive
         * numbers.  We'll put the sign back on the way out.
         */

        if ($x < 0.0)
        {
            $theSign = -1 ;
            $x = -$x ;
        }
        else
        {
            $theSign = 1 ;
        }

        /*
         * Strip off the integer portion, we'll add that back in as we return.
         */

        $theInteger = floor($x) ;
        $x = $x - $theInteger ;

        /*
         * Catch the case of a real number with no fractional part.
         */

        if ($x == 0.0)
        {
            return array($theSign * $theInteger, 1) ;
        }

        /*
         * Take an inital guess at the fractional part of the rational number.
         */

        $d = round(1/$x, 0) ;
        $n = round($x * $d, 0) ;

        do
        {
            /*
             * If our current ratio is "close enough", we're done.
             */

            $delta = abs($x - ($n / $d)) ;

            if ($delta < $theEpsilon)
            {
                $n = $theInteger * $d + $n ;

                /*
                 * return error if the numerator overflows.
                 */

                if ($n < 0)
                {
                    return sqrt(-1) ;
                }

                return array($theSign * $n, $d) ;
            }

            /*
             * Figure out the denominator of the fraction representing
             * the delta.
             */

            $d = round(1/$delta, 0) ;

            /*
             * Check to see if we had an integer overflow in calculating
             * the denominator.  If we did, then bail with Nan.
             */

            if ($d < 0)
            {
                return sqrt(-1) ;
            }

            /*
             * The new guess for the numerator is however many pieces of
             * the denominator are necessary.
             */

            $n = round($x * $d, 0) ;
        } while (true) ;
    }

    /**
     * Produce the prime factors of a number using the sieve of Erastophenes.
     *
     * @desc Factor an integer.
     * @access public
     * @param integer $theNumber the number to be factored.
     * @return array the key is the factor, the value of the array at that point is the number
     *               of times that factor occurs in the output.
     */

    protected static function factor($theNumber)
    {
        $theNumber = abs($theNumber) ;

        /*
         * The upper bound of the prime factors is the square root of the
         * number.
         */

        $upperBound = ceil(sqrt(floatval($theNumber))) ;

        if ($upperBound == 1)
        {
            $theFactors = array(1 => 1) ;
            return $theFactors ;
        }

        /*
         * Generate a list of all possible factors, excluding 1 which is always
         * a factor.
         */

        for ($i = 2; $i <= $upperBound; $i++)
        {
            $theFactors[$i] = 0 ;
        }

        /*
         * Get possible prime factors.
         */

        for ($i = 2; $i <= $upperBound; $i++)
        {
            if (isset($theFactors[$i]))
            {
                for ($j = $i + $i; $j <= $upperBound; $j = $j + $i)
                {
                    unset($theFactors[$j]) ;
                }
            }
        }

        /*
         * Go through the possible factors, counting the ones
         * that exist, eliminating the ones that don't.
         */

        foreach (array_keys($theFactors) as $aFactor)
        {
            if (($theNumber % $aFactor) == 0)
            {
                do
                {
                    $theNumber = $theNumber / $aFactor ;
                    $theFactors[$aFactor]++ ;
                } while (($theNumber % $aFactor) == 0) ;
            }
            else
            {
                unset($theFactors[$aFactor]) ;
            }
        }

        /*
         * One is always a prime factor and it appears once.
         */

        $theFactors[1] = 1 ;

        /*
         * Any residual amount after all the divisions above is also
         * a prime factor and occurs only once.
         */

        $theFactors[$theNumber] = 1 ;

        /*
         * The original input can be reconstructed by taking the sum
         * of the keys raised to the power of their value.
         */

        return $theFactors ;
    }

}
