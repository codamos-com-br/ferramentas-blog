<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'imagem:otimizar',
    description: '',
)]
class ImagemOtimizarCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('entrada', InputArgument::REQUIRED, 'Caminho para a imagem a ser otimizada')
            ->addOption('saida', 's', InputOption::VALUE_OPTIONAL, 'Caminho para escrever a imagem otimizada')
            ->addOption('formato', 'f', InputOption::VALUE_OPTIONAL, 'Formato de saída - ex.: png, jpg, gif', 'jpg')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $imagem = realpath((string) $input->getArgument('entrada'));
        $saida = (string) $input->getOption('saida');
        if (!$saida) {
            $saida = 'php://stdout';
        }

        $formato = (string) $input->getOption('formato');
        $larguraMaxima = 960;

        if (!file_exists($imagem)) {
            $io->error("Arquivo {$imagem} não encontrado.");

            return self::FAILURE;
        }

        $ext = pathinfo($imagem, PATHINFO_EXTENSION);
        $resultado = new StreamOutput(fopen($saida, 'w+'));

        $gd = match ($ext) {
            'jpg', 'jpeg' => imagecreatefromjpeg($imagem),
            'png' => imagecreatefrompng($imagem),
            default => throw new \InvalidArgumentException("Formato {$ext} não suportado."),
        };

        if (!$gd) {
            $io->error('Não foi possível carregar a imagem.');

            return self::FAILURE;
        }

        [$larguraOriginal, $alturaOriginal] = [imagesx($gd), imagesy($gd)];
        $proporcao = $larguraOriginal / $larguraMaxima;
        $largura = (int) ($larguraOriginal / $proporcao);
        $altura = (int) ($alturaOriginal / $proporcao);

        // Converter para true color
        $im = imagecreatetruecolor($largura, $altura);
        imagecopyresampled($im, $gd, 0, 0, 0, 0, $largura, $altura, $larguraOriginal, $alturaOriginal);

        $error = false;
        $outStream = $resultado->getStream();
        switch ($formato) {
            case 'jpg':
            case 'jpeg':
                imageinterlace($gd, true);
                imagejpeg($im, $outStream, 75);
                break;
            case 'png':
                imagesavealpha($im, false);
                imagepng($im, $outStream, 0, PNG_ALL_FILTERS);
                break;
            default:
                $io->error("Formato {$formato} não suportado por esta ferramenta.");
                $error = true;
                break;
        }

        fclose($outStream);
        imagedestroy($gd);
        imagedestroy($im);

        return $error ? self::FAILURE : self::SUCCESS;
    }
}
