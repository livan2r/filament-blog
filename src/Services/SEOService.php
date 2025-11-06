<?php

namespace Firefly\FilamentBlog\Services;

use Illuminate\Support\Str;

class SEOService
{
    protected ?string $title = null;

    protected ?string $description = null;

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Process HTML to add IDs to headers and generate table of contents.
     */
    public function processArticleHtml(string $html): array
    {
        $table = [];
        $dom = new \DOMDocument('1.0', 'UTF-8');

        // Suppress warnings for malformed HTML
        libxml_use_internal_errors(true);

        // Load HTML with UTF-8 encoding
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        // Find all h2 and h3 headers
        $headers = $xpath->query('//h2 | //h3');

        foreach ($headers as $header) {
            $title = trim($header->textContent);

            // Generate a slug for the ID
            $key = Str::slug($title);

            // Ensure unique IDs by appending a number if necessary
            $originalKey = $key;
            $counter = 1;
            while (isset($table[$key])) {
                $key = $originalKey.'-'.$counter;
                $counter++;
            }

            // Set the ID attribute on the header
            $header->setAttribute('id', $key);

            // Add to table of contents
            $table[] = [
                'key' => $key,
                'title' => $title,
                'level' => $header->nodeName, // 'h2' or 'h3'
            ];
        }

        // Get the modified HTML
        $content = $dom->saveHTML();

        // Remove the XML encoding declaration that was added
        $content = preg_replace('/^<\?xml[^>]*>/', '', $content);

        return [
            'table' => $table,
            'content' => $content,
        ];
    }

    /**
     * Replace variables in HTML content.
     *
     * Supported variables:
     * - {APP_URL} => config('app.url')
     * - {LOCALE} => app()->getLocale()
     */
    public function replaceVariables(string $body): string
    {
        $variables = [
            '{APP_URL}' => config('app.url'),
            '{LOCALE}' => app()->getLocale(),
        ];

        return str_replace(array_keys($variables), array_values($variables), $body);
    }
}
