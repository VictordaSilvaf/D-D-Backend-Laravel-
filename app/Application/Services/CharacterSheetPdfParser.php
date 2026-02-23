<?php

declare(strict_types=1);

namespace App\Application\Services;

use Smalot\PdfParser\Parser;

final class CharacterSheetPdfParser
{
    /**
     * Parse a PDF file and return character sheet state + any unrecognized fields.
     *
     * @return array{state: array<string, mixed>, unrecognized: array<int, string>}
     */
    public function parse(string $path): array
    {
        $parser = new Parser;
        $pdf = $parser->parseFile($path);
        $text = $pdf->getText();

        $state = [
            'basics' => [],
            'class' => [],
            'abilities' => [],
            'background' => [],
            'combat' => [],
            'equipment' => [],
        ];

        $unrecognized = [];

        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if ($this->parseBasics($line, $state)) {
                continue;
            }
            if ($this->parseClass($line, $state)) {
                continue;
            }
            if ($this->parseAbilities($line, $state)) {
                continue;
            }
            $this->parseCombat($line, $state);
        }

        $state = array_filter($state, fn ($v) => $v !== [] && $v !== null);

        return [
            'state' => $state,
            'unrecognized' => $unrecognized,
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function parseBasics(string $line, array &$state): bool
    {
        $matched = false;
        if (preg_match('/^(?:character\s+name|nome\s+do\s+personagem)[\s:]*(.+)$/iu', $line, $m)) {
            $state['basics']['character_name'] = trim($m[1]);
            $matched = true;
        } elseif (preg_match('/^(?:player\s+name|nome\s+do\s+jogador)[\s:]*(.+)$/iu', $line, $m)) {
            $state['basics']['player_name'] = trim($m[1]);
            $matched = true;
        } elseif (preg_match('/^(?:race|species|espécie|raça)[\s:]*(.+)$/iu', $line, $m)) {
            $state['basics']['species'] = trim($m[1]);
            $matched = true;
        }

        return $matched;
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function parseClass(string $line, array &$state): bool
    {
        $matched = false;
        if (preg_match('/^(?:class|classe)[\s:]*(.+)$/iu', $line, $m)) {
            $state['class']['name'] = trim($m[1]);
            $matched = true;
        } elseif (preg_match('/^(?:level|nível)[\s:]*(\d+)$/iu', $line, $m)) {
            $state['class']['level'] = (int) $m[1];
            $matched = true;
        }

        return $matched;
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function parseAbilities(string $line, array &$state): bool
    {
        $abilityPatterns = [
            'str' => '/^\s*STR\s*(?:\(?\s*(\d+)\s*\)?)?/iu',
            'dex' => '/^\s*DEX\s*(?:\(?\s*(\d+)\s*\)?)?/iu',
            'con' => '/^\s*CON\s*(?:\(?\s*(\d+)\s*\)?)?/iu',
            'int' => '/^\s*INT\s*(?:\(?\s*(\d+)\s*\)?)?/iu',
            'wis' => '/^\s*WIS\s*(?:\(?\s*(\d+)\s*\)?)?/iu',
            'cha' => '/^\s*CHA\s*(?:\(?\s*(\d+)\s*\)?)?/iu',
        ];

        foreach ($abilityPatterns as $key => $pattern) {
            if (preg_match($pattern, $line, $m) && isset($m[1])) {
                $state['abilities'][$key] = (int) $m[1];

                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function parseCombat(string $line, array &$state): bool
    {
        $matched = false;
        if (preg_match('/^(?:armor\s+class|classe\s+de\s+armadura|AC)[\s:]*(\d+)$/iu', $line, $m)) {
            $state['combat']['ac'] = (int) $m[1];
            $matched = true;
        } elseif (preg_match('/^(?:hit\s+points|pontos\s+de\s+vida|HP)[\s:]*(\d+)/iu', $line, $m)) {
            $state['combat']['hp_max'] = (int) $m[1];
            $state['combat']['hp_current'] = (int) $m[1];
            $matched = true;
        }

        return $matched;
    }
}
