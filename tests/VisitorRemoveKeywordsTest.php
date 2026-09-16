<?php

namespace Lorisleiva\LaravelSearchString\Tests;

use Lorisleiva\LaravelSearchString\Visitors\AttachRulesVisitor;
use Lorisleiva\LaravelSearchString\Visitors\InlineDumpVisitor;
use Lorisleiva\LaravelSearchString\Visitors\RemoveKeywordsVisitor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * @see RemoveKeywordsVisitor
 */
class VisitorRemoveKeywordsTest extends VisitorTest
{
    public function visitors($manager, $builder, $model)
    {
        return [
            new AttachRulesVisitor($manager),
            new RemoveKeywordsVisitor(),
            new InlineDumpVisitor(),
        ];
    }

    public static function success()
    {
        return [
            // It transforms keyword queries into empty symbols.
            ['foo:bar', '/^foo$/', 'EMPTY'],
            ['foo in (1,2,3)', '/f/', 'EMPTY'],

            // It leaves queries that do not match intact.
            ['foo:1,2', '/^baz$/', 'LIST(foo in [1, 2])'],
            ['foo:"Hello world"', 'f', 'QUERY(foo = Hello world)'],

            // It does not fetch keywords inside relationship symbols.
            ['comment: (limit:10)', 'limit', 'EXISTS(comment, QUERY(limit = 10))'],
            ['comment: (limit:10) limit:10', 'limit', 'AND(EXISTS(comment, QUERY(limit = 10)), EMPTY)'],
        ];
    }

    #[Test]
    #[DataProvider('success')]
    public function visitor_remove_keywords_success($input, $rule, $expected)
    {
        $model = $this->getModelWithKeywords(['banana_keyword' => $rule]);
        $this->assertAstEquals($input, $expected, $model);
    }
}
