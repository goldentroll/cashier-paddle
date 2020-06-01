<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Laravel\Paddle\Exceptions\InvalidTransaction;
use Laravel\Paddle\Transaction;
use Money\Currency;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\User;

class TransactionTest extends TestCase
{
    public function test_it_returns_an_empty_collection_if_the_user_is_not_a_customer_yet()
    {
        $billable = new User();

        $transactions = $billable->transactions();

        $this->assertCount(0, $transactions);
    }

    public function test_it_throws_an_exception_for_an_invalid_owner()
    {
        $billable = new User(['paddle_id' => 1]);
        $transaction = ['user' => ['user_id' => 2]];

        $this->expectException(InvalidTransaction::class);

        new Transaction($billable, $transaction);
    }

    public function test_it_can_returns_its_user()
    {
        $billable = new User(['paddle_id' => 1]);
        $transaction = new Transaction($billable, [
            'user' => ['user_id' => 1],
        ]);

        $this->assertSame($billable, $transaction->user());
    }

    public function it_can_return_its_currency()
    {
        $billable = new User(['paddle_id' => 1]);
        $transaction = new Transaction($billable, [
            'user' => ['user_id' => 1],
            'currency' => 'EUR',
        ]);
        $currency = $transaction->currency();

        $this->assertInstanceOf(Currency::class, $currency);
        $this->assertInstanceOf('EUR', $currency->getCode());
    }

    public function test_it_can_returns_its_receipt_url()
    {
        $billable = new User(['paddle_id' => 1]);
        $transaction = new Transaction($billable, [
            'user' => ['user_id' => 1],
            'receipt_url' => 'https://example.com/receipt.pdf',
        ]);

        $this->assertSame('https://example.com/receipt.pdf', $transaction->receipt());
    }

    public function test_it_can_returns_its_created_at_timestamp()
    {
        $billable = new User(['paddle_id' => 1]);
        $transaction = new Transaction($billable, [
            'user' => ['user_id' => 1],
            'created_at' => '2020-05-07 10:53:17',
        ]);

        $this->assertInstanceOf(Carbon::class, $transaction->date());
        $this->assertSame('2020-05-07 10:53:17', $transaction->date()->format('Y-m-d H:i:s'));
    }

    public function test_it_can_determine_if_it_is_a_subscription_transaction()
    {
        $billable = new User(['paddle_id' => 1]);
        $transaction = new Transaction($billable, [
            'user' => ['user_id' => 1],
            'is_subscription' => true,
            'is_one_off' => false,
        ]);

        $this->assertTrue($transaction->isSubscription());
        $this->assertFalse($transaction->isOneOff());
    }

    public function test_it_can_determine_if_it_is_a_one_off_transaction()
    {
        $billable = new User(['paddle_id' => 1]);
        $transaction = new Transaction($billable, [
            'user' => ['user_id' => 1],
            'is_subscription' => false,
            'is_one_off' => true,
        ]);

        $this->assertFalse($transaction->isSubscription());
        $this->assertTrue($transaction->isOneOff());
    }

    public function test_it_implements_arrayable_and_jsonable()
    {
        $billable = new User(['paddle_id' => 1]);
        $transaction = new Transaction($billable, $data =[
            'user' => ['user_id' => 1],
            'is_subscription' => false,
            'is_one_off' => true,
        ]);

        $this->assertSame($data, $transaction->toArray());
        $this->assertSame($data, $transaction->jsonSerialize());
        $this->assertSame(json_encode($data), $transaction->toJson());
    }
}
