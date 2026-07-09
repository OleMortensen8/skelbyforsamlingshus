<?php

namespace App;

use SimpleXMLElement;

class EventManager
{
    private function normalizeDescription(string $text): string
    {
        // Normalisér linjeskift
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // " - " betyder ny linje (med mellemrum så 2005-2025 ikke rammes)
        $text = str_replace(" - ", "\n", $text);

        // Hvis der findes <br> i teksten, så behandl det som linjeskift
        $text = preg_replace('~<br\s*/?>~i', "\n", $text);

        // Fjern øvrige tags (vi vil selv styre HTML-output)
        $text = strip_tags($text);

        // Trim hver linje
        $lines = array_map('trim', explode("\n", $text));

        // Fjern tomme linjer i start/slut
        while (count($lines) && $lines[0] === '') {
            array_shift($lines);
        }
        while (count($lines) && end($lines) === '') {
            array_pop($lines);
        }

        return implode("\n", $lines);
    }

    private function splitIntroAndAgenda(string $normalizedText): array
    {
        // Find dagsorden-markør (case-insensitive)
        $marker = 'Dagsorden er som følger';
        $pos = mb_stripos($normalizedText, $marker, 0, 'UTF-8');

        if ($pos === false) {
            return [
                'intro' => trim($normalizedText),
                'agendaText' => ''
            ];
        }

        $intro = trim(mb_substr($normalizedText, 0, $pos, 'UTF-8'));
        $after = trim(mb_substr($normalizedText, $pos + mb_strlen($marker, 'UTF-8'), null, 'UTF-8'));

        // Fjern indledende ":" og whitespace efter markøren
        $after = ltrim($after);
        $after = preg_replace('~^[:\s]+~u', '', $after);

        return [
            'intro' => $intro,
            'agendaText' => $after
        ];
    }

    private function parseAgenda(string $agendaText): array
    {
        $agendaText = trim($agendaText);
        if ($agendaText === '') {
            return [];
        }

        // Convert inline subpoints like "... >a. ..." into line breaks before subpoint markers
        $agendaText = preg_replace(
            '~\s*>\s*(?=[a-zA-ZæøåÆØÅ]\s*[\.\)\:])~u',
            "\n",
            $agendaText
        );

        $lines = preg_split('~\n+~', $agendaText);

        $items = [];
        $currentIndex = -1;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            // Hovedpunkter: "1.Valg..." eller "1. Valg..." eller "1) Valg..." eller "1: Valg..."
            if (preg_match('~^[>\s]*?(\d+)\s*[\.\)\:]\s*(.*)$~u', $line, $m)) {
                $text = trim($m[2]);

                // Ignorér tomme punkter som "7." / "8." / "9."
                if ($text === '') {
                    $currentIndex = -1;
                    continue;
                }

                $items[] = [
                    'text' => $text,
                    'sub' => []
                ];
                $currentIndex = count($items) - 1;
                continue;
            }

            // Underpunkter: "a. ..." / "b) ..." / "c: ..."
            // (knyttes til seneste hovedpunkt)
            if (preg_match('~^[>\s]*([a-zA-ZæøåÆØÅ])\s*[\.\)\:]\s*(.+)$~u', $line, $m) && $currentIndex >= 0) {
                $items[$currentIndex]['sub'][] = trim($m[2]);
                continue;
            }

            // Fortsættelseslinje: læg til seneste hovedpunkt
            if ($currentIndex >= 0) {
                $items[$currentIndex]['text'] .= ' ' . $line;
            }
        }

        return $items;
    }

    private function agendaToHtml(array $agendaItems): string
    {
        if (empty($agendaItems)) {
            return '';
        }

        $html = "<ol class=\"agenda-ol\">";
        foreach ($agendaItems as $item) {
            $html .= "<li>" . htmlspecialchars($item['text'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            if (!empty($item['sub'])) {
                $html .= "<ol class=\"agenda-ol\" type=\"a\">";
                foreach ($item['sub'] as $subItem) {
                    $html .= "<li>" . htmlspecialchars($subItem, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</li>";
                }
                $html .= "</ol>";
            }

            $html .= "</li>";
        }
        $html .= "</ol>";

        return $html;
    }

    private function descriptionToHtml(string $raw): string
    {
        $normalized = $this->normalizeDescription($raw);
        $parts = $this->splitIntroAndAgenda($normalized);

        $introHtml = '';
        if ($parts['intro'] !== '') {
            $introHtml = nl2br(
                htmlspecialchars($parts['intro'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                false
            );
        }

        $agendaItems = $this->parseAgenda($parts['agendaText']);
        $agendaHtml = $this->agendaToHtml($agendaItems);

        if ($agendaHtml !== '') {
            $out = '';
            if ($introHtml !== '') {
                $out .= "<div class='beskrivelse-intro'>{$introHtml}</div>";
            }
            $out .= "<div class='beskrivelse-dagsorden'><h4>Dagsorden</h4>{$agendaHtml}</div>";
            return $out;
        }

        return "<div class='beskrivelse-intro'>{$introHtml}</div>";
    }

    public function getArangementer(SimpleXMLElement $xml): string
    {
        if (!$xml->children()) {
            return "<h2>Arrangementsliste forberedes...</h2>";
        }

        $text = "";
        foreach ($xml->children() as $arrangement) {
            $text .= "<div class='arrangement'>";

            $imageSrc = trim((string)$arrangement->image);
            if ($imageSrc !== '') {
                $text .= "<div class='arrangement-image'><img src='" . htmlspecialchars($imageSrc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "' alt='" . htmlspecialchars((string)$arrangement->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "'></div>";
            }

            $text .= "<div class='arrangement-body'>";
            $text .= "<h2>" . htmlspecialchars((string)$arrangement->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</h2>";
            $text .= "<h3>Dato: " . htmlspecialchars((string)$arrangement->date, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</h3>";
            $text .= "<h3>Tid: " . htmlspecialchars((string)$arrangement->time, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</h3>";
            $text .= "<h3>Stedet: " . htmlspecialchars((string)$arrangement->location, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</h3>";

            $rawDescription = (string)$arrangement->description;
            $text .= "<h3>Beskrivelse:</h3>";
            $text .= "<div style='font-weight:bold;' class='beskrivelse'>" . $this->descriptionToHtml($rawDescription) . "</div>";
            $text .= "</div>";

            $text .= "</div><hr>";
        }
        return $text;
    }
}
