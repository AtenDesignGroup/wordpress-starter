<?php
defined( 'ABSPATH' ) or die( "You can't access this file directly." );
/**
 * RTF parser/formatter
 *
 * This code reads RTF files and formats the RTF data to HTML.
 * Original from: https://github.com/henck/rtf-html-php
 *
 * PHP version 5
 *
 * @author     Alexander van Oostenrijk
 * @copyright  2014 Alexander van Oostenrijk
 * @license    GNU
 * @version    1
 * @link       http://www.independent-software.com
 *
 * Sample of use:
 *
 * $reader = new ASP_RtfReader();
 * $rtf = file_get_contents("test.rtf"); // or use a string
 * $reader->Parse($rtf);
 * //$reader->root->dump(); // to see what the reader read
 * $formatter = new ASP_RtfHtml();
 * echo $formatter->Format($reader->root);
 *
 * -----------------------------------------------------------
 * Notice: Class names have been prefixed with 'ASP_' to avoid compatibility issiues
 * (namespaces cannot be used, as WordPress supports php5.2)
 */
class ASP_RtfElement {
    protected function Indent($level) {
        for ($i = 0; $i < $level * 2; $i++) echo "&nbsp;";
    }
}

class ASP_RtfGroup extends ASP_RtfElement {
    public $parent;
    public $children;

    public function __construct() {
        $this->parent = null;
        $this->children = array();
    }

    public function GetType() {
        // No children?
        if (sizeof($this->children) == 0) return null;
        // First child not a control word?
        $child = $this->children[0];
        if (!$child instanceof ASP_RtfControlWord) return null;

        return $child->word;
    }

    public function IsDestination() {
        // No children?
        if (sizeof($this->children) == 0) return null;
        // First child not a control symbol?
        $child = $this->children[0];
        if (!$child instanceof ASP_RtfControlSymbol) return null;

        return $child->symbol == '*';
    }

    public function dump($level = 0) {
        echo "<div>";
        $this->Indent($level);
        echo "{";
        echo "</div>";

        foreach ($this->children as $child) {
            if ($child instanceof ASP_RtfGroup) {
                if ($child->GetType() == "fonttbl") continue;
                if ($child->GetType() == "colortbl") continue;
                if ($child->GetType() == "stylesheet") continue;
                if ($child->GetType() == "info") continue;
                // Skip any pictures:
                if (substr($child->GetType(), 0, 4) == "pict") continue;
                if ($child->IsDestination()) continue;
            }
            $child->dump($level + 2);
        }

        echo "<div>";
        $this->Indent($level);
        echo "}";
        echo "</div>";
    }
}

class ASP_RtfControlWord extends ASP_RtfElement {
    public $word;
    public $parameter;

    public function dump($level) {
        echo "<div style='color:green'>";
        $this->Indent($level);
        echo "WORD {$this->word} ({$this->parameter})";
        echo "</div>";
    }
}

class ASP_RtfControlSymbol extends ASP_RtfElement {
    public $symbol;
    public $parameter = 0;

    public function dump($level) {
        echo "<div style='color:blue'>";
        $this->Indent($level);
        echo "SYMBOL {$this->symbol} ({$this->parameter})";
        echo "</div>";
    }
}

class ASP_RtfText extends ASP_RtfElement {
    public $text;

    public function dump($level) {
        echo "<div style='color:red'>";
        $this->Indent($level);
        echo "TEXT {$this->text}";
        echo "</div>";
    }
}

class ASP_RtfReader {
    public $root = null;

    protected function GetChar() {
        $this->char = null;
        if ($this->pos < strlen($this->rtf)) {
            $this->char = $this->rtf[$this->pos++];
        } else {
            $this->err = "Tried to read past EOF, RTF is probably truncated";
        }
    }

    protected function ParseStartGroup() {
        // Store state of document on stack.
        $group = new ASP_RtfGroup();
        if ($this->group != null) $group->parent = $this->group;
        if ($this->root == null) {
            $this->group = $group;
            $this->root = $group;
        } else {
            array_push($this->group->children, $group);
            $this->group = $group;
        }
    }

    protected function is_letter() {
        if (ord($this->char) >= 65 && ord($this->char) <= 90) return TRUE;
        if (ord($this->char) >= 97 && ord($this->char) <= 122) return TRUE;

        return FALSE;
    }

    protected function is_digit() {
        if (ord($this->char) >= 48 && ord($this->char) <= 57) return TRUE;

        return FALSE;
    }

    protected function ParseEndGroup() {
        // Retrieve state of document from stack.
        $this->group = $this->group->parent;
    }

    protected function ParseControlWord() {
        $this->GetChar();
        $word = "";

        while ($this->is_letter()) {
            $word .= $this->char;
            $this->GetChar();
        }

        // Read parameter (if any) consisting of digits.
        // Paramater may be negative.
        $parameter = null;
        $negative = false;
        if ($this->char == '-') {
            $this->GetChar();
            $negative = true;
        }
        while ($this->is_digit()) {
            if ($parameter == null) $parameter = 0;
            $parameter = $parameter * 10 + $this->char;
            $this->GetChar();
        }
        if ($parameter === null) $parameter = 1;
        if ($negative) $parameter = -$parameter;

        // If this is \u, then the parameter will be followed by
        // a character.
        if ($word == "u") {
            // Ignore space delimiter
            if ($this->char == ' ') $this->GetChar();

            // if the replacement character is encoded as
            // hexadecimal value \'hh then jump over it
            if ($this->char == '\\' && $this->rtf[$this->pos] == '\'')
                $this->pos = $this->pos + 3;

            // Convert to UTF unsigned decimal code
            if ($negative) $parameter = 65536 + $parameter;
        }
        // If the current character is a space, then
        // it is a delimiter. It is consumed.
        // If it's not a space, then it's part of the next
        // item in the text, so put the character back.
        else {
            if ($this->char != ' ') $this->pos--;
        }

        $rtfword = new ASP_RtfControlWord();
        $rtfword->word = $word;
        $rtfword->parameter = $parameter;
        array_push($this->group->children, $rtfword);
    }

    protected function ParseControlSymbol() {
        // Read symbol (one character only).
        $this->GetChar();
        $symbol = $this->char;

        // Symbols ordinarily have no parameter. However,
        // if this is \', then it is followed by a 2-digit hex-code:
        $parameter = 0;
        if ($symbol == '\'') {
            $this->GetChar();
            $parameter = $this->char;
            $this->GetChar();
            $parameter = hexdec($parameter . $this->char);
        }

        $rtfsymbol = new ASP_RtfControlSymbol();
        $rtfsymbol->symbol = $symbol;
        $rtfsymbol->parameter = $parameter;
        array_push($this->group->children, $rtfsymbol);
    }

    protected function ParseControl() {
        // Beginning of an RTF control word or control symbol.
        // Look ahead by one character to see if it starts with
        // a letter (control world) or another symbol (control symbol):
        $this->GetChar();
        $this->pos--;
        if ($this->is_letter())
            $this->ParseControlWord();
        else
            $this->ParseControlSymbol();
    }

    protected function ParseText() {
        // Parse plain text up to backslash or brace,
        // unless escaped.
        $text = "";

        do {
            $terminate = false;
            $escape = false;

            // Is this an escape?
            if ($this->char == '\\') {
                // Perform lookahead to see if this
                // is really an escape sequence.
                $this->GetChar();
                switch ($this->char) {
                    case '\\':
                        $text .= '\\';
                        break;
                    case '{':
                        $text .= '{';
                        break;
                    case '}':
                        $text .= '}';
                        break;
                    default:
                        // Not an escape. Roll back.
                        $this->pos = $this->pos - 2;
                        $terminate = true;
                        break;
                }
            } else if ($this->char == '{' || $this->char == '}') {
                $this->pos--;
                $terminate = true;
            }

            if (!$terminate && !$escape) {
                $text .= $this->char;
                $this->GetChar();
            }
        } while (!$terminate && $this->pos < $this->len);

        $rtftext = new ASP_RtfText();
        $rtftext->text = $text;

        // If group does not exist, then this is not a valid RTF file. Throw an exception.
        if ($this->group == NULL) {
            throw new Exception();
        }

        array_push($this->group->children, $rtftext);
    }

    /*
     * Attempt to parse an RTF string. Parsing returns TRUE on success or FALSE on failure
     */
    public function Parse($rtf) {
        if ( function_exists( 'mb_internal_encoding' ) ) {
            mb_internal_encoding( "UTF-8" );
        }

        try {
            $this->rtf = utf8_encode( $rtf );
            $this->pos = 0;
            $this->len = strlen($this->rtf);
            $this->group = null;
            $this->root = null;

            while ($this->pos < $this->len) {
                // Read next character:
                $this->GetChar();

                // Ignore \r and \n
                if ($this->char == "\n" || $this->char == "\r") continue;

                // What type of character is this?
                switch ($this->char) {
                    case '{':
                        $this->ParseStartGroup();
                        break;
                    case '}':
                        $this->ParseEndGroup();
                        break;
                    case '\\':
                        $this->ParseControl();
                        break;
                    default:
                        $this->ParseText();
                        break;
                }
            }

            return TRUE;
        } catch (Exception $ex) {
            return FALSE;
        }
    }
}

class ASP_RtfState {
    public function __construct() {
        $this->Reset();
    }

    public function Reset() {
        $this->bold = false;
        $this->italic = false;
        $this->underline = false;
        $this->end_underline = false;
        $this->strike = false;
        $this->hidden = false;
        $this->fontsize = 0;
    }
}

class ASP_RtfHtml {
    public function Format($root) {
        $this->output = "";
        // Keeping track of style modifications
        $this->previousState = null;
        $this->openedTags = array('span' => False, 'p' => False);
        // Create a stack of states:
        $this->states = array();
        // Put an initial standard state onto the stack:
        $this->state = new ASP_RtfState();
        array_push($this->states, $this->state);
        $this->FormatGroup($root);

        return $this->output;
    }

    protected function FormatGroup($group) {
        // Can we ignore this group?
        if ($group->GetType() == "fonttbl") return;
        elseif ($group->GetType() == "colortbl") return;
        elseif ($group->GetType() == "stylesheet") return;
        elseif ($group->GetType() == "info") return;
        // Skip any pictures:
        if (substr($group->GetType(), 0, 4) == "pict") return;
        if ($group->IsDestination()) return;

        // Push a new state onto the stack:
        $this->state = clone $this->state;
        array_push($this->states, $this->state);

        foreach ($group->children as $child) {
            if ($child instanceof ASP_RtfGroup) $this->FormatGroup($child);
            elseif ($child instanceof ASP_RtfControlWord) $this->FormatControlWord($child);
            elseif ($child instanceof ASP_RtfControlSymbol) $this->FormatControlSymbol($child);
            elseif ($child instanceof ASP_RtfText) $this->FormatText($child);
        }

        // Pop state from stack.
        array_pop($this->states);
        $this->state = $this->states[sizeof($this->states) - 1];
    }

    protected function FormatControlWord($word) {
        if ($word->word == "plain") $this->state->Reset();
        elseif ($word->word == "b") $this->state->bold = $word->parameter;
        elseif ($word->word == "i") $this->state->italic = $word->parameter;
        elseif ($word->word == "ul") $this->state->underline = $word->parameter;
        elseif ($word->word == "ulnone") $this->state->end_underline = $word->parameter;
        elseif ($word->word == "strike") $this->state->strike = $word->parameter;
        elseif ($word->word == "v") $this->state->hidden = $word->parameter;
        elseif ($word->word == "fs") $this->state->fontsize = ceil(($word->parameter / 24) * 16);

        elseif ($word->word == "par") {
            // close previously opened 'span' tag
            $this->CloseTag();
            // decide whether to open or to close a 'p' tag
            if ($this->openedTags["p"]) $this->CloseTag("p");
            else {
                $this->output .= "<p>";
                $this->openedTags['p'] = True;
            }
        } // Characters:
        elseif ($word->word == "lquote") $this->output .= "&lsquo;";
        elseif ($word->word == "rquote") $this->output .= "&rsquo;";
        elseif ($word->word == "ldblquote") $this->output .= "&ldquo;";
        elseif ($word->word == "rdblquote") $this->output .= "&rdquo;";
        elseif ($word->word == "emdash") $this->output .= "&mdash;";
        elseif ($word->word == "endash") $this->output .= "&ndash;";
        elseif ($word->word == "bullet") $this->output .= "&bull;";
        // Print Unicode character
        elseif ($word->word == "u")
            $this->ApplyStyle("&#" . $word->parameter . ";");
    }

    protected function ApplyStyle($txt) {
        // create span only when a style change occur
        if ($this->previousState != $this->state) {
            $span = "";
            if ($this->state->bold) $span .= "font-weight:bold;";
            if ($this->state->italic) $span .= "font-style:italic;";
            if ($this->state->underline) $span .= "text-decoration:underline;";
            if ($this->state->end_underline) $span .= "text-decoration:none;";
            if ($this->state->strike) $span .= "text-decoration:strikethrough;";
            if ($this->state->hidden) $span .= "display:none;";
            if ($this->state->fontsize != 0) $span .= "font-size: {$this->state->fontsize}px;";

            // Keep track of preceding style
            $this->previousState = clone $this->state;

            // close previously opened 'span' tag
            $this->CloseTag();

            $this->output .= "<span style=\"{$span}\">" . $txt;
            $this->openedTags["span"] = True;
        } else $this->output .= $txt;
    }

    protected function CloseTag($tag = "span") {
        if ($this->openedTags[$tag]) {
            $this->output .= "</{$tag}>";
            $this->openedTags[$tag] = False;
        }
    }

    protected function FormatControlSymbol($symbol) {
        if ($symbol->symbol == '\'')
            $this->ApplyStyle(htmlentities(chr($symbol->parameter), ENT_QUOTES, 'UTF-8'));
    }

    protected function FormatText($text) {
        $this->ApplyStyle($text->text);
    }
}