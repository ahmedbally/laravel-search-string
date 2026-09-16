<?php

namespace Lorisleiva\LaravelSearchString\Tests;

use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;

class DumpCommandsTest extends TestCase
{
    #[Test]
    public function it_dumps_the_ast()
    {
        $this->assertEquals(
            <<<EOL
            AND
            >   name = A
            >   price > 10
            EOL,
            $this->ast('name: A price > 10')
        );

        $this->assertEquals(
            <<<EOL
            EXISTS [comments]
            >   EXISTS [author]
            >   >   name = John
            EOL,
            $this->ast('comments.author.name = John')
        );
    }

    #[Test]
    public function it_dumps_the_sql_query()
    {
        $this->assertEquals(
            'select * from `products` where (`products`.`name` = A and `products`.`price` > 10)',
            $this->sql('name: A price > 10')
        );

        $this->assertEquals(
            'select * from `products` where exists (select * from `comments` where `products`.`id` = `comments`.`product_id` and exists (select * from `users` where `comments`.`user_id` = `users`.`id` and `users`.`name` = John))',
            $this->sql('comments.author.name = John')
        );

        $this->assertEquals(
            'select * from `products` where ((`products`.`name` like %A% or `products`.`description` like %A%) or (`products`.`name` like %B% or `products`.`description` like %B%))',
            $this->sql('A or B')
        );
    }

    public function ast(string $query)
    {
        return $this->runDumpCommand('ast', $query);
    }

    public function sql(string $query)
    {
        return $this->runDumpCommand('sql', $query);
    }

    public function results(string $query)
    {
        return $this->runDumpCommand('get', $query);
    }

    public function runDumpCommand(string $type, string $query)
    {
        Artisan::call(sprintf('search-string:%s /Lorisleiva/LaravelSearchString/Tests/Stubs/Product "%s"', $type, $query));

        return trim(Artisan::output(), "\r\n");
    }
}
