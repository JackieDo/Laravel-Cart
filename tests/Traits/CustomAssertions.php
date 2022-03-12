<?php

trait CustomAssertions
{
    /**
     * Use expectException() method with differents PHPUnit version.
     *
     * @param string $exception
     *
     * @return void
     */
    public function assertException($exception)
    {
        if (method_exists(get_parent_class($this), 'expectException')) {
            parent::expectException($exception);
        } else {
            $this->setExpectedException($exception);
        }
    }

    /**
     * Asserts that two associative arrays are similar.
     *
     * Both arrays must have the same indexes with identical values
     * without respect to key ordering
     *
     * @param array $expected
     * @param array $array
     */
    public function assertSimilarArray(array $expected, array $array)
    {
        $this->assertEquals(count($expected), count($array));
        $this->assertTrue(0 === count(array_diff_key($array, $expected)));

        foreach ($expected as $key => $value) {
            if (is_array($value)) {
                $this->assertSimilarArray($value, $array[$key]);
            } else {
                $this->assertContains($value, $array);
            }
        }
    }
}
