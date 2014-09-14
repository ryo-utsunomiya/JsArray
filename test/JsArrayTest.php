<?php

namespace MyArray;

require_once __DIR__ . '/../JsArray.php';

/**
 * Class JsArrayTest
 *
 * @package JsArray
 */
class JsArrayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function コンストラクタに1つだけ引数を渡すとその値を配列のサイズとみなす()
    {
        $this->assertEquals(100, (new JsArray(100))->size);
    }

    /**
     * @test
     */
    public function コンストラクタには可変長の引数を渡せる()
    {
        $result = true;
        try {
            new JsArray(1);
            new JsArray(1, 2);
            new JsArray('a', 'b', 'c');
        } catch (\Exception $e) {
            $result = false;
        }
        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function clone前と後のオブジェクトは別物になる()
    {
        $arr      = new JsArray();
        $clone    = clone $arr;
        $clone[0] = 'foo';

        $this->assertEmpty($arr[0]);
    }

    /**
     * @test
     */
    public function JsArrayは配列の記法でアクセスすると指定されたインデックスの値を返す()
    {
        $arr = new JsArray(0, 1);

        $this->assertEquals(1, $arr[1]);
    }

    /**
     * @test
     */
    public function 添字がマイナスの場合は末尾から遡って返す()
    {
        $arr = new JsArray(0, 1, 2);

        $this->assertEquals(2, $arr[-1]);
    }

    /**
     * @test
     */
    public function 存在しない添字を指定するとissetでfalseが返る()
    {
        $arr = new JsArray();

        $this->assertFalse(isset($arr[999]));
    }

    /**
     * @test
     */
    public function JsArrayに配列の記法で値をセットできる()
    {
        $arr    = new JsArray();
        $arr[0] = 'a';

        $this->assertEquals($arr[0], 'a');
    }

    /**
     * @test
     */
    public function 添字がマイナスの場合は末尾から遡ってセットする()
    {
        $arr     = new JsArray(0, 1, 2);
        $arr[-1] = 'a';

        $this->assertEquals($arr[2], 'a');
    }

    /**
     * @test
     */
    public function 添字が配列のサイズより大きい場合は配列を拡張する()
    {
        $arr    = new JsArray();
        $arr[2] = 'a';

        $this->assertEquals($arr[2], 'a');
    }

    /**
     * @test
     */
    public function 添字が空の場合は新しく要素が追加される()
    {
        $arr   = new JsArray();
        $arr[] = 'a';

        $this->assertEquals('a', $arr[0]);
    }

    /**
     * @test
     */
    public function offsetUnsetは要素を取り除く()
    {
        $arr = new JsArray(0, 1);
        $arr->offsetUnset(0);

        $this->assertFalse(isset($arr[0]));
    }

    /**
     * @test
     */
    public function toArrayはネイティブの配列を返す()
    {
        $this->assertEquals([0, 1], (new JsArray(0, 1))->toArray());
    }

    /**
     * @test
     */
    public function pushは配列の末尾に要素を入れる()
    {
        $arr = new JsArray();
        $arr->push('a');

        $this->assertEquals('a', $arr[0]);
    }

    /**
     * @test
     */
    public function pushの引数には複数の要素を指定できる()
    {
        $arr = new JsArray();
        $arr->push('a', 'b', 'c');

        $this->assertEquals('c', $arr[2]);
    }


    /**
     * @test
     */
    public function pushは操作後の配列のサイズを返す()
    {
        $this->assertEquals(1, (new JsArray())->push('a'));
    }


    /**
     * @test
     */
    public function popは配列の末尾から要素を取り出す()
    {
        $arr = new JsArray(0, 1, 2);

        $this->assertEquals($arr->pop(), 2);
        $this->assertEquals(2, $arr->size);
    }

    /**
     * @test
     */
    public function shiftは配列の先頭から要素を取り出す()
    {
        $arr = new JsArray('a', 'b', 'c');

        $this->assertEquals('a', $arr->shift());
        $this->assertEquals(2, $arr->size);
    }

    /**
     * @test
     */
    public function unshiftは配列の先頭に要素を追加する()
    {
        $arr = new JsArray('a', 'b', 'c');
        $arr->unshift('z');

        $this->assertEquals('z', $arr[0]);
        $this->assertEquals(4, $arr->size);
    }

    /**
     * @test
     */
    public function splice()
    {
        $arr = new JsArray(0, 1);

        $arr->splice($arr->size, 0, 2);
        $this->assertEquals([0, 1, 2], $arr->toArray());

        $ret = $arr->splice(-1, 1);
        $this->assertEquals(2, $ret[0]);
        $this->assertEquals([0, 1], $arr->toArray()); // @todo 2番目の要素が内部で残っている

        $ret = $arr->splice(0, 1);
        $this->assertEquals(0, $ret[0]);
        $this->assertEquals([1], $arr->toArray());

        $arr->splice(0, 0, 'zero');
        $this->assertEquals(['zero', 1], $arr->toArray());

        $arr->splice(0, 1, 0);
        $this->assertEquals([0, 1], $arr->toArray());
    }

    /**
     * @test
     */
    public function reverseは配列の要素の並び順を反転する()
    {
        $arr = new JsArray(0, 1);
        $arr->reverse();

        $this->assertEquals([1, 0], $arr->toArray());
    }

    /**
     * @test
     */
    public function concatは配列に新しい要素を付け足して返す()
    {
        $arr = new JsArray();
        $arr->concat(1, 2);

        $this->assertEquals(new JsArray(), $arr);
        $this->assertEmpty($arr[0]);

        $this->assertEquals([0], (new JsArray())->concat(0)->toArray());
        $this->assertEquals([0, 1], (new JsArray())->concat(0, 1)->toArray());
        $this->assertEquals([0, 1], (new JsArray())->concat([0, 1])->toArray());

        $obj      = new \stdClass();
        $obj->foo = 'bar';
        $this->assertEquals(['bar'], (new JsArray())->concat($obj)->toArray());
    }

    /**
     * @test
     */
    public function joinは配列を結合して文字列にして返す()
    {
        $this->assertEquals('abc', (new JsArray('a', 'b', 'c'))->join());
    }

    /**
     * @test
     */
    public function joinにglue文字列を渡すとその文字で接着した文字列を返す()
    {
        $this->assertEquals('a,b,c', (new JsArray('a', 'b', 'c'))->join(','));
    }

    /**
     * @test
     */
    public function toStringは配列を結合して文字列にして返す()
    {
        $this->assertEquals('abc', (new JsArray('a', 'b', 'c'))->toString());
    }

    /**
     * @test
     */
    public function sliceは配列の一部を取り出して新しい配列を返す()
    {
        $fruits = new JsArray("Banana", "Orange", "Lemon", "Apple", "Mango");
        $citrus = $fruits->slice(1, 3);
        $this->assertEquals(["Orange", "Lemon"], $citrus->toArray());
    }
    
    /**
     * @test
     */
    public function sortは引数なしの場合辞書順に並べて返す()
    {
        $arr = new JsArray('banana', 'apple');
        $arr->sort();

        $this->assertEquals(['apple', 'banana'], $arr->toArray());
    }


}
 
