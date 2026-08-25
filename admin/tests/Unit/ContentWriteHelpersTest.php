<?php

namespace Tests\Unit;

use App\Exceptions\ContentWriteException;
use App\Services\ContentWriteService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Input coercion and containment rules used by every content write. These are
 * the parts that decide what an AI client is allowed to put in the database, so
 * they are covered independently of the database itself.
 */
class ContentWriteHelpersTest extends TestCase
{
    private function call(string $method, array $args): mixed
    {
        $reflection = new ReflectionMethod(ContentWriteService::class, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs(null, $args);
    }

    /* ------------------------------------------------------------- booleans */

    public static function booleanShapes(): array
    {
        return [
            'true'         => [true, false, true],
            'false'        => [false, true, false],
            'string true'  => ['true', false, true],
            'string on'    => ['on', false, true],
            'string yes'   => ['yes', false, true],
            'string 1'     => ['1', false, true],
            'string false' => ['false', true, false],
            'string off'   => ['off', true, false],
            'int 1'        => [1, false, true],
            'int 0'        => [0, true, false],
        ];
    }

    /**
     * @dataProvider booleanShapes
     */
    public function test_enable_flags_accept_the_shapes_llm_clients_send(mixed $given, bool $default, bool $expected): void
    {
        $this->assertSame($expected, $this->call('boolInput', [['enable' => $given], 'enable', $default]));
    }

    public function test_absent_or_null_flag_falls_back_to_the_default(): void
    {
        $this->assertTrue($this->call('boolInput', [[], 'enable', true]));
        $this->assertFalse($this->call('boolInput', [[], 'enable', false]));
        $this->assertTrue($this->call('boolInput', [['enable' => null], 'enable', true]));
    }

    /* ---------------------------------------------------------------- dates */

    public function test_created_at_accepts_a_plain_date_and_a_full_datetime(): void
    {
        $this->assertSame('2024-03-01 00:00:00', $this->call('parseDate', [['created_at' => '2024-03-01'], 'created_at']));
        $this->assertSame('2024-03-01 14:30:00', $this->call('parseDate', [['created_at' => '2024-03-01 14:30:00'], 'created_at']));
    }

    public function test_absent_created_at_means_leave_it_alone(): void
    {
        $this->assertNull($this->call('parseDate', [[], 'created_at']));
        $this->assertNull($this->call('parseDate', [['created_at' => ''], 'created_at']));
        $this->assertNull($this->call('parseDate', [['created_at' => null], 'created_at']));
    }

    public function test_an_unreadable_date_is_rejected_with_the_expected_format(): void
    {
        $this->expectException(ContentWriteException::class);
        $this->expectExceptionMessageMatches('/YYYY-MM-DD/');

        $this->call('parseDate', [['created_at' => 'last tuesday-ish'], 'created_at']);
    }

    /* --------------------------------------------------------------- images */

    public function test_images_are_optional(): void
    {
        $this->assertSame('', $this->call('normalizeImage', ['']));
        $this->assertSame('', $this->call('normalizeImage', [null]));
    }

    public function test_absolute_urls_pass_through(): void
    {
        $this->assertSame('https://cdn.example.com/a.png', $this->call('normalizeImage', ['https://cdn.example.com/a.png']));
        $this->assertSame('http://example.test/a.png', $this->call('normalizeImage', ['http://example.test/a.png']));
    }

    public function test_upload_paths_are_normalised_to_a_single_form(): void
    {
        $this->assertSame('uploads/img/articles/a.png', $this->call('normalizeImage', ['uploads/img/articles/a.png']));
        $this->assertSame('uploads/img/articles/a.png', $this->call('normalizeImage', ['img/articles/a.png']));
        $this->assertSame('uploads/img/projects/b.jpg', $this->call('normalizeImage', ['/uploads/img/projects/b.jpg']));
    }

    public static function rejectedImagePaths(): array
    {
        return [
            'absolute system path' => ['/etc/passwd'],
            'parent traversal'     => ['../../../etc/passwd'],
            'embedded traversal'   => ['uploads/../../secret.png'],
            'outside img dir'      => ['storage/private/x.png'],
        ];
    }

    /**
     * @dataProvider rejectedImagePaths
     */
    public function test_paths_outside_the_uploads_image_tree_are_refused(string $path): void
    {
        $this->expectException(ContentWriteException::class);

        $this->call('normalizeImage', [$path]);
    }

    /* ----------------------------------------------------------- kind maps */

    public function test_category_kinds_map_to_their_tables(): void
    {
        $this->assertSame('article_categories', $this->call('categoryTable', ['article']));
        $this->assertSame('project_categories', $this->call('categoryTable', ['project']));
    }

    public function test_an_unknown_category_kind_is_refused(): void
    {
        $this->expectException(ContentWriteException::class);

        $this->call('categoryTable', ['bogus']);
    }

    public function test_about_kinds_map_to_their_tables(): void
    {
        foreach (['skill', 'experience', 'service', 'testimonial'] as $kind) {
            $this->assertSame($kind, $this->call('aboutTable', [$kind]));
        }
    }

    public function test_an_unknown_about_kind_names_the_valid_kinds(): void
    {
        $this->expectException(ContentWriteException::class);
        $this->expectExceptionMessageMatches('/testimonial/');

        $this->call('aboutTable', ['bogus']);
    }

    /* ------------------------------------------------------------------ html */

    public static function dangerousHtml(): array
    {
        return [
            'inline script'      => ['<p>hi</p><script>alert(1)</script>', 'alert'],
            'external script'    => ['<p>a</p><script src="//evil.test/x.js"></script>', 'script'],
            'uppercase script'   => ['<SCRIPT>bad()</SCRIPT>', 'script'],
            'orphan close tag'   => ['<p>a</p></script>', 'script'],
            'onerror attribute'  => ['<img src=x onerror="steal()">', 'onerror'],
            'onclick attribute'  => ["<div onclick='go()'>x</div>", 'onclick'],
            'unquoted handler'   => ['<div onmouseover=go()>x</div>', 'onmouseover'],
            'javascript url'     => ['<a href="javascript:evil()">x</a>', 'javascript:'],
            'iframe'             => ['<iframe src="//evil.test"></iframe>', 'iframe'],
            'form'               => ['<form action="/x"><input></form>', '<form'],
        ];
    }

    /**
     * Article bodies are rendered with {@html} on the public site, so these must
     * not survive the write.
     *
     * @dataProvider dangerousHtml
     */
    public function test_dangerous_html_is_stripped_from_bodies(string $html, string $needle): void
    {
        $this->assertStringNotContainsStringIgnoringCase(
            $needle,
            $this->call('sanitizeHtml', [$html])
        );
    }

    public static function legitimateHtml(): array
    {
        return [
            'escaped code sample' => ['<pre><code>if (a &lt; b &amp;&amp; c &gt; d) { return &lt;T&gt;(x); }</code></pre>'],
            'article markup'      => ['<h2>Title</h2><p>Text with <strong>bold</strong> and <a href="/x">link</a>.</p><ul><li>one</li></ul>'],
            'image'               => ['<img src="uploads/img/articles/a.png" alt="a" />'],
            'table'               => ['<table><thead><tr><th>a</th></tr></thead><tbody><tr><td>1</td></tr></tbody></table>'],
            'video'               => ['<video controls><source src="/v.mp4" type="video/mp4"></video>'],
            'prose with tag word' => ['<p>I wrote a description of my transcription work.</p>'],
            'prose starting on'   => ['<p>Later on we shipped it. Only one thing left.</p>'],
        ];
    }

    /**
     * The whole reason this path skips the XSS middleware is that its strip_tags
     * pass mangles escaped angle brackets in code samples.
     *
     * @dataProvider legitimateHtml
     */
    public function test_legitimate_markup_survives_untouched(string $html): void
    {
        $this->assertSame($html, $this->call('sanitizeHtml', [$html]));
    }

    /* -------------------------------------------------------------- strings */

    public function test_blank_strings_are_treated_as_absent(): void
    {
        $this->assertNull($this->call('nonEmpty', ['   ']));
        $this->assertNull($this->call('nonEmpty', [null]));
        $this->assertSame('hi', $this->call('nonEmpty', ['  hi  ']));
    }
}
