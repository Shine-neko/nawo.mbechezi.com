<?php

declare(strict_types=1);

namespace App\Command;

use App\Parser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Twig\Environment;

#[AsCommand('site:build')]
class BuildCommand extends Command
{
    private Environment $twig;
    private Parser $parser;
    public const string ROOT_DIR = __DIR__ . '/../..';
    public const string DEFAULT_LOCALE = 'en';
    public const array LOCALES = ['en', 'fr'];

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
        $this->parser = new Parser();

        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this->addOption('watch', 'w', InputOption::VALUE_NONE, 'Watch for changes and rebuild automatically');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $watch = $input->getOption('watch');

        $this->build($output);

        if ($watch) {
            $output->writeln('<info>Watching for changes... Press Ctrl+C to stop.</info>');
            $this->watch($output);
        }

        return Command::SUCCESS;
    }

    private function build(OutputInterface $output): void
    {
        $output->writeln('<comment>Building site...</comment>');
        foreach (self::LOCALES as $locale) {
            $this->createPages($locale);
        }
        $this->createPosts();
        $this->createFragments();
        $this->createFeed();
        $output->writeln('<info>Build complete!</info>');
    }

    private function watch(OutputInterface $output): void
    {
        $signature = $this->getSourceSignature();

        while (true) {
            sleep(1);
            clearstatcache();
            $current = $this->getSourceSignature();

            if ($current !== $signature) {
                $output->writeln('<comment>Change detected, rebuilding...</comment>');
                $this->build($output);
                $signature = $this->getSourceSignature();
            }
        }
    }

    private function getSourceSignature(): string
    {
        $parts = [];
        $directories = [
            self::ROOT_DIR . '/data/_pages',
            self::ROOT_DIR . '/data/_posts',
            self::ROOT_DIR . '/data/_fragments',
            self::ROOT_DIR . '/templates',
        ];

        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                continue;
            }

            foreach (Finder::create()->files()->in($directory)->sortByName() as $file) {
                $parts[] = $file->getRelativePathname() . ':' . $file->getMTime() . ':' . $file->getSize();
            }
        }

        return md5(implode('|', $parts));
    }

    private function createPages(string $locale): void
    {
        $pagesDir = $locale === self::DEFAULT_LOCALE
            ? self::ROOT_DIR . '/data/_pages'
            : self::ROOT_DIR . '/data/_pages/' . $locale;

        if (!is_dir($pagesDir)) {
            return;
        }

        $outputBase = $locale === self::DEFAULT_LOCALE
            ? self::ROOT_DIR . '/public/'
            : self::ROOT_DIR . '/public/' . $locale . '/';

        if (!is_dir($outputBase)) {
            mkdir($outputBase, 0o755, true);
        }

        $finder = Finder::create()->files()->name('*.md')->depth(0)->in($pagesDir);

        foreach ($finder as $page) {
            $data = $this->parser->parse($page->getContents());
            $data['lang'] = $locale;
            $data['alt_lang'] = $this->getAltLang($locale);
            $data['alt_url'] = $this->getAltUrl($locale, $data['permalink']);

            if (isset($data['layout'])) {
                $data['posts'] = $this->fetchPosts($data['layout'], $locale);
            }

            $template = $data['template'] ?? 'page.html.twig';
            $html = $this->twig->render($template, $data);

            file_put_contents($outputBase . $data['permalink'], $html);
        }
    }

    private function getAltLang(string $locale): string
    {
        return $locale === 'en' ? 'fr' : 'en';
    }

    private function getAltUrl(string $locale, string $permalink): string
    {
        $alt = $this->getAltLang($locale);
        return $alt === self::DEFAULT_LOCALE ? '/' . $permalink : '/' . $alt . '/' . $permalink;
    }

    private function createPosts(): void
    {
        $folder = self::ROOT_DIR . '/public/posts/';
        if (!is_dir($folder)) {
            mkdir($folder, 0o755, true);
        }

        foreach (Finder::create()->files()->in(self::ROOT_DIR . '/data/_posts') as $file) {
            $data = $this->parser->parse($file->getContents());
            $regex = "/[0-9]{4}-[0-9]{2}-[0-9]{2}/";
            preg_match($regex, $file->getRelativePathname(), $matches);

            $data['created_at'] = new \DateTime($matches[0]);
            $data['lang'] = $data['lang'] ?? self::DEFAULT_LOCALE;
            $data['alt_lang'] = $this->getAltLang($data['lang']);
            $data['alt_url'] = $data['lang'] === self::DEFAULT_LOCALE ? '/fr/blog.html' : '/blog.html';

            $html = $this->twig->render('post/post.html.twig', $data);
            $filename = str_replace('md', 'html', $file->getFilename());

            file_put_contents($folder . $filename, $html);
        }
    }

    private function createFragments(): void
    {
        $fragmentsDir = self::ROOT_DIR . '/data/_fragments';

        if (!is_dir($fragmentsDir)) {
            return;
        }

        $folder = self::ROOT_DIR . '/public/fragments/';
        if (!is_dir($folder)) {
            mkdir($folder, 0o755, true);
        }

        foreach (Finder::create()->files()->in($fragmentsDir) as $file) {
            $data = $this->parser->parse($file->getContents());
            $regex = "/[0-9]{4}-[0-9]{2}-[0-9]{2}/";
            preg_match($regex, $file->getRelativePathname(), $matches);

            $data['created_at'] = new \DateTime($matches[0]);
            $data['lang'] = $data['lang'] ?? self::DEFAULT_LOCALE;
            $data['alt_lang'] = $this->getAltLang($data['lang']);
            $data['alt_url'] = $data['lang'] === self::DEFAULT_LOCALE ? '/fr/fragments.html' : '/fragments.html';

            $html = $this->twig->render('post/post.html.twig', $data);
            $filename = str_replace('md', 'html', $file->getFilename());

            file_put_contents($folder . $filename, $html);
        }
    }

    private function createFeed(): void
    {
        $siteUrl = 'https://nawo.mbechezi.com';
        $items = [];

        foreach (Finder::create()->files()->in(self::ROOT_DIR . '/data/_posts') as $file) {
            $data = $this->parser->parse($file->getContents());
            $regex = "/[0-9]{4}-[0-9]{2}-[0-9]{2}/";
            preg_match($regex, $file->getRelativePathname(), $matches);

            $items[] = [
                'title' => $data['title'] ?? 'Untitled',
                'date' => new \DateTime($matches[0] ?? 'now'),
                'link' => $siteUrl . '/posts/' . str_replace('.md', '.html', $file->getFilename()),
                'description' => $data['content'] ?? '',
            ];
        }

        usort($items, static fn($a, $b) => $b['date'] <=> $a['date']);

        $itemsXml = '';
        foreach ($items as $item) {
            $itemsXml .= sprintf(
                "        <item>\n            <title>%s</title>\n            <link>%s</link>\n            <guid isPermaLink=\"true\">%s</guid>\n            <pubDate>%s</pubDate>\n            <description><![CDATA[%s]]></description>\n        </item>\n",
                htmlspecialchars($item['title'], ENT_XML1 | ENT_QUOTES, 'UTF-8'),
                $item['link'],
                $item['link'],
                $item['date']->format(\DateTimeInterface::RSS),
                $item['description']
            );
        }

        $buildDate = (new \DateTime())->format(\DateTimeInterface::RSS);
        $rss = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>Nawo — Blog</title>
        <link>{$siteUrl}</link>
        <atom:link href="{$siteUrl}/feed.xml" rel="self" type="application/rss+xml" />
        <description>Notes from Mlanawo Mbechezi — VCto, architect, builder.</description>
        <language>en</language>
        <lastBuildDate>{$buildDate}</lastBuildDate>
{$itemsXml}    </channel>
</rss>
XML;

        file_put_contents(self::ROOT_DIR . '/public/feed.xml', $rss);
    }

    private function fetchPosts(?string $category, string $locale): array
    {
        $posts = [];
        $directories = [
            self::ROOT_DIR . '/data/_posts' => '/posts/',
            self::ROOT_DIR . '/data/_fragments' => '/fragments/',
        ];

        foreach ($directories as $directory => $urlPrefix) {
            if (!is_dir($directory)) {
                continue;
            }

            /** @var SplFileInfo $file */
            foreach (Finder::create()->files()->in($directory) as $file) {
                $regex = "/[0-9]{4}-[0-9]{2}-[0-9]{2}/";
                preg_match($regex, $file->getRelativePathname(), $matches);

                $data = $this->parser->parse($file->getContents());
                $filename = str_replace('md', 'html', $file->getFilename());
                $postLang = $data['lang'] ?? self::DEFAULT_LOCALE;

                if (null !== $category && ($data['layout'] ?? null) !== $category) {
                    continue;
                }

                if ($postLang !== $locale) {
                    continue;
                }

                $posts[] = [
                    'title' => $data['title'],
                    'created_at' => new \DateTime($matches[0]),
                    'permalink' => $urlPrefix . $filename,
                ];
            }
        }

        usort($posts, static fn($a, $b) => $b['created_at'] <=> $a['created_at']);

        return $posts;
    }
}
