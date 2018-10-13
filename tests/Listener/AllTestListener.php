<?php

namespace App\Tests\Listener;

use App\Tests\Helpers\DatabaseBootstrap;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Framework\AssertionFailedError;

class AllTestListener implements TestListener
{
    /**
     * @var DatabaseBootstrap
     */
    protected $databaseBootstrap;

    public function addError(Test $test, \Exception $e, $time)
    {
    }

    public function addWarning(Test $test, Warning $e, $time)
    {
    }

    public function addFailure(Test $test, AssertionFailedError $e, $time)
    {
    }

    public function addIncompleteTest(Test $test, \Exception $e, $time)
    {
    }

    public function addRiskyTest(Test $test, \Exception $e, $time)
    {
    }

    public function addSkippedTest(Test $test, \Exception $e, $time)
    {
    }

    public function startTest(Test $test)
    {
    }

    public function endTest(Test $test, $time)
    {
    }

    public function startTestSuite(TestSuite $suite)
    {
        if ($suite->getName() == 'all') {
            $this->databaseBootstrap = new DatabaseBootstrap();
            $this->databaseBootstrap->up();
            printf("TestSuite '%s' started.\n", $suite->getName());
        }
    }

    public function endTestSuite(TestSuite $suite)
    {
        if ($suite->getName() == 'all') {
            printf("TestSuite '%s' ended.\n", $suite->getName());
            $this->databaseBootstrap->down();
        }
    }
}
?>