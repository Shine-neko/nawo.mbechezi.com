<?php
declare(strict_types=1);

namespace App\Command;

use App\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Twig\Environment;

class PressCommand extends Command
{
    protected static $defaultName = 'site:build';
    private Environment $twig;
    private Parser $parser;
    public const ROOT_DIR = __DIR__ . '/../..';

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
        foreach (Finder::create()->in(self::ROOT_DIR . '/data/_pages') as $page) {
            $data = $this->parser->parse($page->getContents());

            if (isset($data['layout'])) {
                $posts = $this->fetchPosts($data['layout']);
                $data['posts'] = $posts;
            }

            $template = $data['template'] ?? 'page.html.twig';
            $html = $this->twig->render($template, $data);

            file_put_contents(self::ROOT_DIR . '/public/' . $data['permalink'], $html);
        }
    }

    private function createPosts(): void
    {
        foreach (Finder::create()->in(self::ROOT_DIR . '/data/_posts') as $file) {
            $data = $this->parser->parse($file->getContents());
            $regex = "/[0-9]{4}-[0-9]{2}-[0-9]{2}/";
            preg_match($regex, $file->getRelativePathname(), $matches);

            $data['created_at'] = new \DateTime($matches[0]);

            $html = $this->twig->render('post/post.html.twig', $data);
            $filename = str_replace('md', 'html', $file->getFilename());
            $folder = self::ROOT_DIR . '/public/posts/';

            if (!@mkdir($folder) && !is_dir($folder)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $folder));
            }

            file_put_contents($folder . $filename, $html);
        }
    }

    private function fetchPosts(?string $category): array
    {
        $posts = [];
        /** @var SplFileInfo $file */
        foreach (Finder::create()->in(self::ROOT_DIR . '/data/_posts') as $file) {
            $regex = "/[0-9]{4}-[0-9]{2}-[0-9]{2}/";
            preg_match($regex, $file->getRelativePathname(), $matches);

            $data = $this->parser->parse($file->getContents());
            $filename = str_replace('md', 'html', $file->getFilename());

            if (null !== $category && $data['layout'] !== $category) {
                continue;
            }

            $posts[] = [
                'title' => $data['title'],
                'created_at' => new \DateTime($matches[0]),
                'permalink' => '/posts/'.$filename
            ];
        }

        return $posts;
    }
}
