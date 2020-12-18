<?php
declare(strict_types=1);

namespace App\Command;

use App\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Twig\Environment;
use Symfony\Component\Yaml\Yaml;

class PressCommand extends Command
{
    protected static $defaultName = 'app:site:press';
    private Environment $twig;
    private Parser $parser;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
        $this->parser = new Parser();

        parent::__construct(null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->createPages();
        $this->createPosts();

        return Command::SUCCESS;
    }

    private function createPages(): void
    {
        foreach (Finder::create()->in(__DIR__ . '/../../data/_pages') as $page) {
            $data = $this->parser->parse($page->getContents());

            if (isset($data['layout'])) {
                $posts = $this->fetchPosts($data['layout']);
                $data['posts'] = $posts;
            }

            $template = $data['template'] ?? 'page.html.twig';
            $html = $this->twig->render($template, $data);

            file_put_contents(__DIR__ . '/../../public/' . $data['permalink'], $html);
        }
    }

    private function createPosts(): void
    {
        foreach (Finder::create()->in(__DIR__ . '/../../data/_posts') as $file) {
            $data = $this->parser->parse($file->getContents());

            $html = $this->twig->render('post/post.html.twig', $data);
            $filename = str_replace('md', 'html', $file->getFilename());
            $folder = __DIR__ . '/../../public/posts/';

            if (!@mkdir($folder) && !is_dir($folder)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $folder));
            }

            file_put_contents($folder . $filename, $html);
        }
    }

    private function fetchPosts(?string $category): array
    {
        $posts = [];
        foreach (Finder::create()->in(__DIR__ . '/../../data/_posts') as $file) {
            $data = $this->parser->parse($file->getContents());
            $filename = str_replace('md', 'html', $file->getFilename());

            if (null !== $category && $data['layout'] !== $category) {
                continue;
            }

            $posts[] = [
                'title' => $data['title'],
                'permalink' => '/posts/'.$filename
            ];
        }

        return $posts;
    }
}
