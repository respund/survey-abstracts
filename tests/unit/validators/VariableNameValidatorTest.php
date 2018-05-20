<?php


namespace dameter\abstracts\tests\unit\validators;

use dameter\abstracts\validators\VariableNameValidator;
use yii\base\DynamicModel;

/**
 * Class VariableNameValidatorTest
 * @package dameter\abstracts\tests\validators
 * @author Tõnis Ormisson <tonis@andmemasin.eu>
 */
class VariableNameValidatorTest extends \Codeception\Test\Unit
{

    /**
     * @return array [value, isValid, skipPrivateMethods]
     */
    public function provideValues()
    {
        return [
            [['an array'], false],
            ["String with spaces", false],
            [true, false],
            [false, false],

            ["Q1", true],
            ["with-dashes", true],
            ["with_underscores", true],
            ["with.fullstop", true],

            // value with exactly 64 characters
            ["CCDD1598CA2E0A715818561E49F2FBF9DADACBF1F5E75951956CBE0F3AE14393", true],
            // value with exactly 64+1 characters
            ["CCDD1598CA2E0A715818561E49F2FBF9DADACBF1F5E75951956CBE0F3AE143931", false],
            // value with exactly 64-1 characters
            ["CCDD1598CA2E0A715818561E49F2FBF9DADACBF1F5E75951956CBE0F3AE1439", true],

            // non alpha first letters
            ["1", false],
            ["@", false], // allowed by SPSS, but not this
            ["#", false], // allowed by SPSS, but not this
            ['$', false], // allowed by SPSS, but not this
            ["Ä", false],
            ["-", false],
            ["_", false],
            [".", false],
            ["Щ", false], // cyrillic
            ["漢", false], // chinese

            // contains invalid chars
            ["some,punctuation", false],
            ["some%punctuation", false],
            ["some*punctuation", false],
            ["some\punctuation", false],
            ["good是一个在中国的字符串", false], // chinese
            ["goodданные", false], // cyrillic

        ];

    }


    /**
     * @param mixed $value
     * @param boolean $isValid
     * @dataProvider provideValues
     */
    public function testValidateValue($value, $isValid)
    {
        $validator = new VariableNameValidator();

        $message = "Failed with value " . serialize($value);
        if ($isValid) {
            $this->assertTrue($validator->validate($value), $message);
        } else {
            $this->assertFalse($validator->validate($value), $message);
        }
    }

    /**
     * @param mixed $value
     * @param boolean $isValid
     * @dataProvider provideValues
     */
    public function testValidateAttribute($value, $isValid)
    {
        $validator = new VariableNameValidator();

        $model = new DynamicModel(['myAttribute' => $value]);
        $model->addRule('myAttribute', VariableNameValidator::class)->validate();
        $validator->validateAttribute($model, 'myAttribute');
        $message = "Failed with value " . serialize($value);
        if ($isValid) {
            $this->assertEmpty($model->errors, $message);
        } else {
            $this->assertNotEmpty($model->errors, $message);
        }
    }

    /**
     * @param mixed $value
     * @param boolean $isValid
     * @dataProvider provideValues
     * @return null
     * @throws \ReflectionException
     */
    public function testContainsInvalidCharacters($value, $isValid){


        $validator = new VariableNameValidator();
        // thi is a private method test, all values won't reach it
        if ((is_string($value) && strlen($value) === 1) or !is_string($value) or (is_string($value) && strlen($value) > $validator->max)) {
            $this->assertTrue(true);
            return null;
        }

        $method = new \ReflectionMethod(VariableNameValidator::class, 'containsInvalidCharacters');
        $method->setAccessible(true);
        $message = "Failed with value " . serialize($value);

        $result = $method->invokeArgs($validator, [$value]);
        if ($isValid) {
            $this->assertFalse($result, $message);
        } else {
            $this->assertTrue($result, $message);
        }
        return null;
    }


}
