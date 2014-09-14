<?php

namespace MyArray;

/**
 * Class JsArray
 *
 * @package MyArray
 * @see     http://blog.livedoor.jp/dankogai/archives/50961373.html
 */
class JsArray implements \ArrayAccess
{
    /** @var int Array size */
    public $size;

    /** @var \stdClass Array data */
    protected $data;

    /**
     * @param $arrayLike
     *
     * @return JsArray
     */
    public static function from($arrayLike)
    {
        $result = new self();
        if (is_array($arrayLike)) {
            foreach ($arrayLike as $val) {
                $result->offsetSet($result->size, $val);
            }
        }

        return $result;
    }

    /**
     * @param ...$args
     */
    public function __construct()
    {
        $args    = func_get_args();
        $argsLen = count($args);

        if ($argsLen === 1 && is_int($args[0])) { // 引数が1つだけかつ整数の場合
            $this->size = $args[0]; // 引数の値を配列のサイズとみなす
        } else {
            $this->size = $argsLen;
        }

        $this->data = new \stdClass(); // [] でない点に注意!
        for ($i = 0; $i < $argsLen; $i++) {
            $this->data->$i = $args[$i];
        }
    }

    /**
     * @param int $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        $offset = (int)$offset;

        return isset($this->data->$offset);
    }

    /**
     * @param int $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $offset = (int)$offset;
        if ($offset < 0) { // 添字がマイナスの場合
            $offset += $this->size; // 必要に応じてサイズ変更
        }
        if ($this->size < $offset + 1) {
            return null;
        }

        return $this->data->$offset;
    }

    /**
     * @param int   $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) { // 添字を空にした場合（例：$arr[] = 'a'）、$offsetにはnullが渡される
            $offset = $this->size; // 添字がからの場合は配列の末尾に追加する
        } else {
            $offset = (int)$offset;
        }
        if ($offset < 0) {
            $offset = $offset + $this->size;
        }
        if ($offset >= $this->size) {
            $this->size = $offset + 1;
        }
        $this->data->$offset = $value;
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        $offset = (int)$offset;

        unset($this->data->$offset);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        if (!$this->size) {
            return [];
        }
        $result = [];
        for ($i = 0, $l = $this->size; $i < $l; $i++) {
            $result[$i] = $this->offsetGet($i);
        }

        return $result;
    }

    /**
     * @param ...$args
     *
     * @return int
     */
    public function push()
    {
        foreach (func_get_args() as $arg) {
            $this->offsetSet($this->size, $arg);
        }

        return $this->size;
    }

    /**
     * @return mixed
     */
    public function pop()
    {
        if (!$this->size) { // 空ならnullをreturn
            return null;
        }
        $value = $this->offsetGet($this->size - 1); // 最後の要素
        $this->size--; // 要素数を一つ減らす

        return $value;
    }

    /**
     * @todo 実装アルゴリズムをリストに変更
     *
     * @return mixed
     */
    public function shift()
    {
        if (!$this->size) {
            return null;
        }
        $value = $this[0];
        // 後でデータを一つ前に引っ越す
        for ($i = 0, $l = $this->size - 1; $i < $l; $i++) {
            $this->offsetSet($i, $this[$i + 1]);
        }
        $this->size--;

        return $value;
    }

    /**
     * @param $value
     *
     * @todo 実装アルゴリズムをリストに変更
     *
     * @return $this
     */
    public function unshift($value)
    {
        // 先にデータを一つ先に引っ越す
        for ($i = $this->size - 1; $i >= 0; $i--) {
            $this->offsetSet($i + 1, $this[$i]);
        }
        $this[0] = $value;

        return $this;
    }

    /**
     * @param int $where
     * @param int $len
     * @param ...$args
     *
     * @return JsArray
     */
    public function splice($where, $len)
    {
        $where = (int)$where;
        $len   = (int)$len;
        $args  = func_get_args();

        if ($where < 0) { // 位置が負数の場合
            $where = $this->size + $where;
        }

        $result = new self(); // 結果もMyArrayで

        // まずはwhereからlen個の要素をコピー
        for ($i = 0; $i < $len; $i++) {
            $result->offsetSet($i, $this->offsetGet($where + $i));
        }

        // len個だけ前につめる
        // @todo 上手くつめられていないパターンがある。 @see MyArrayTest::splice()
        for ($i = $where + $len, $l = $this->size; $i < $l; $i++) {
            $this->offsetSet($i - $len, $this->offsetGet($i));
        }
        $this->size -= $len;

        // 追加要素がある場合
        if (count($args) > 2) {
            $stretch = count($args) - 2;
            // 隙間を開ける
            for ($i = $this->size - 1; $i >= $where; $i--) {
                $this->offsetSet($i + $stretch, $this->offsetGet($i));
            }
            // 隙間に追加要素をコピー
            for ($i = 0, $l = $stretch; $i < $l; $i++) {
                $this->offsetSet($where + $i, $args[$i + 2]);
            }
        }

        return $result;
    }

    /**
     * @return JsArray
     */
    public function reverse()
    {
        $result = new self();
        for ($i = 0, $l = $this->size; $i < $l; $i++) {
            $result->offsetSet($i, $this->offsetGet($l - $i - 1));
        }
        $this->data = clone $result->data;
    }

    /**
     * @return JsArray
     */
    public function concat()
    {
        $result = $this->cloneSelf();
        foreach (func_get_args() as $arg) {
            if (is_array($arg) || is_object($arg)) {
                foreach ($arg as $v) {
                    $result = $result->concat($v);
                }
            } else {
                $result->offsetSet($result->size, $arg);
            }
        }

        return $result;
    }

    /**
     * @param callable $function
     *
     * @return $this
     */
    public function sort(Callable $function = null) // @todo function利用を実装
    {
        $arr = $this->toArray();
        sort($arr); // @todo ソートアルゴリズム実装

        $this->data = self::from($arr)->data;
    }

    /**
     * @param string $separator
     *
     * @return string
     */
    public function join($separator = '')
    {
        return implode($separator, $this->toArray());
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->join();
    }

    /**
     * @param int      $begin
     * @param null|int $end
     *
     * @return JsArray
     */
    public function slice($begin, $end = null)
    {
        $result = new self();
        $l      = is_null($end) ? $this->size : $end;
        for ($i = $begin; $i < $l; $i++) {
            $result->offsetSet($result->size, $this->offsetGet($i));
        }

        return $result;
    }

    /**
     * @todo 実装
     *
     * @param      $searchElement
     * @param null $fromIndex
     */
    public function indexOf($searchElement, $fromIndex = null)
    {
    }

    /**
     * @todo 実装
     *
     * @param      $searchElement
     * @param null $fromIndex
     */
    public function lastIndexOf($searchElement, $fromIndex = null)
    {
    }

    /**
     * @return JsArray
     */
    private function cloneSelf()
    {
        $clone       = new self();
        $clone->data = clone $this->data;
        $clone->size = $this->size;

        return $clone;
    }

}
