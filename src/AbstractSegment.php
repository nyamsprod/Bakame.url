<?php
/**
* This file is part of the League.url library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/url/
* @version 4.0.0
* @package League.url
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace League\Url;

use InvalidArgumentException;
use OutOfBoundsException;

/**
 *  A class to manipulate URL Segment like components
 *
 *  @package League.url
 *  @since  3.0.0
 */
abstract class AbstractSegment extends AbstractContainer
{
    /**
     * segment delimiter
     *
     * @var string
     */
    protected $delimiter;

    /**
     * The Constructor
     * @param mixed $data The data to add
     */
    public function __construct($data = null)
    {
        $this->set($data);
    }

    /**
     * {@inheritdoc}
     */
    public function set($data)
    {
        $this->data = array_filter($this->validate($data), function ($value) {
            return ! is_null($value);
        });
				
				return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSegment($offset, $default = null)
    {
        $offset = filter_var($offset, FILTER_VALIDATE_INT, ['options' => ["min_range" => 0]]);
        if (false === $offset || ! isset($this->data[$offset])) {
            return $default;
        }

        return $this->data[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function setSegment($offset, $value)
    {
        $offset = filter_var($offset, FILTER_VALIDATE_INT, ['options' => [
            "min_range" => 0,
            "max_range" => $this->count(),
        ]]);
        if (false === $offset) {
            throw new OutOfBoundsException('The specified key is not in the object boundaries');
        }

        $data = $this->data;
        $value = filter_var((string) $value, FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_STRIP_LOW]);
        $value = trim($value);

        if (empty($value)) {
            unset($data[$offset]);
            return $this->set(array_values($data));
        }

        $data[$offset] = $value;

        return $this->set(array_values($data));
    }

    /**
     * {@inheritdoc}
     * @param string|array|\Traversable $data the data
     */
    public function remove($data)
    {
        $data = $this->fetchRemainingSegment($this->data, $data);
        if (! is_null($data)) {
            $this->set($data);

            return true;
        }
        return false;
    }

    /**
     * Sanitize a string component recursively
     *
     * @param mixed $str
     *
     * @return mixed
     */
    protected function sanitizeValue($str)
    {
        if (is_array($str)) {
            foreach ($str as &$value) {
                $value = $this->sanitizeValue($value);
            }
            unset($value);

            return $str;
        }

        $str = filter_var((string) $str, FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_STRIP_LOW]);
        $str = trim($str);

        return $str;
    }

    /**
     * Validate a component
     *
     * @param mixed $data the component value to be validate
     *
     * @return array
     *
     * @throws \InvalidArgumentException If The data is invalid
     */
    abstract protected function validate($data);

    /**
     * {@inheritdoc}
     * @param string|array|\Traversable $data
     * @param string                    $whence
     * @param integer                   $whence_index
		 * 
		 * @return static $this for chaining
     */
    public function append($data, $whence = null, $whence_index = null)
    {
        $this->data = $this->appendSegment(
            $this->data,
            $this->validate($data),
            $whence,
            $whence_index
        );
				
				return $this;
    }

    /**
     * {@inheritdoc}
     * @param string|array|\Traversable $data
     * @param string                    $whence
     * @param integer                   $whence_index
		 * 
		 * @return static $this for chaining
     */
    public function prepend($data, $whence = null, $whence_index = null)
    {
        $this->data = $this->prependSegment(
            $this->data,
            $this->validate($data),
            $whence,
            $whence_index
        );
				
				return $this;
    }

    /**
     * Format removing component labels
     *
     * @param string|array|\Traversable $data the component value to be validate
     *
     * @return array
     */
    protected function formatRemoveSegment($data)
    {
        return $this->sanitizeValue($this->validateSegment($data));
    }

    /**
     * Validate data before insertion into a URL segment based component
     *
     * @param mixed $data the data to insert
     *
     * @return array
     *
     * @throws \RuntimeException if the data is not valid
     */
    protected function validateSegment($data)
    {
        return $this->convertToArray($data, function ($str) {
            if ('' == $str) {
                return [];
            }
            if ($this->delimiter == $str[0]) {
                $str = substr($str, 1);
            }

            return explode($this->delimiter, $str);
        });
    }

    /**
     * Append some data to a given array
     *
     * @param array   $left         the original array
     * @param array   $value        the data to prepend
     * @param string  $whence       the value of the data to prepend before
     * @param integer $whence_index the occurrence index for $whence
     *
     * @return array
     */
    protected function appendSegment(array $left, array $value, $whence = null, $whence_index = null)
    {
        $right = [];
        if (null !== $whence && count($found = array_keys($left, $whence))) {
            array_reverse($found);
            $index = $found[0];
            if (array_key_exists($whence_index, $found)) {
                $index = $found[$whence_index];
            }
            $right = array_slice($left, $index+1);
            $left = array_slice($left, 0, $index+1);
        }

        return array_merge($left, $value, $right);
    }

    /**
     * Prepend some data to a given array
     *
     * @param array   $right        the original array
     * @param array   $value        the data to prepend
     * @param string  $whence       the value of the data to prepend before
     * @param integer $whence_index the occurrence index for $whence
     *
     * @return array
     */
    protected function prependSegment(array $right, array $value, $whence = null, $whence_index = null)
    {
        $left = [];
        if (null !== $whence && count($found = array_keys($right, $whence))) {
            $index = $found[0];
            if (array_key_exists($whence_index, $found)) {
                $index = $found[$whence_index];
            }
            $left = array_slice($right, 0, $index);
            $right = array_slice($right, $index);
        }

        return array_merge($left, $value, $right);
    }

    /**
     * Remove some data from a given array
     *
     * @param array $data  the original array
     * @param mixed $value the data to be removed (can be an array or a single segment)
     *
     * @return string|null
     *
     * @throws \RuntimeException If $value is invalid
     */
    protected function fetchRemainingSegment(array $data, $value)
    {
        $segment = implode($this->delimiter, $data);
        if ('' == $value) {
            if ($index = array_search('', $data, true)) {
                $left = array_slice($data, 0, $index);
                $right = array_slice($data, $index+1);

                return implode($this->delimiter, array_merge($left, $right));
            }

            return $segment;
        }

        $part = implode($this->delimiter, $this->formatRemoveSegment($value));

        $regexStart = '@(:?^|\\'.$this->delimiter.')';

        if (! preg_match($regexStart.preg_quote($part, '@').'@', $segment, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $pos = $matches[0][1];

        return substr($segment, 0, $pos).substr($segment, $pos + strlen($part) + 1);
    }
}
