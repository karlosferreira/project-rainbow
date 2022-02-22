<?php

namespace Apps\Core_Comments\Installation\Data;


defined('PHPFOX') or exit('NO DICE!');

/**
 * Class v412
 * @package Apps\Core_Comments\Installation\Data
 */
class v412
{
    protected $unicodeList;

    public function __construct()
    {
        $this->unicodeList = [
            '(waving)' => '\uD83D\uDC4B',
            '(OK)' => '\uD83D\uDC4C',
            '(y)' => '\uD83D\uDC4D',
            '(n)' => '\uD83D\uDC4E',
            '(clap)' => '\uD83D\uDC4F',
            '(smiling)' => '\uD83D\uDE0A',
            '(savoring)' => '\uD83D\uDE0B',
            '(relieved)' => '\uD83D\uDE0C',
            '(hearteyes)' => '\uD83D\uDE0D',
            '(cool)' => '\uD83D\uDE0E',
            '(smirking)' => '\uD83D\uDE0F',
            '(kiss)' => '\uD83D\uDE1A',
            ':P' => '\uD83D\uDE0B',
            '(disappointed)' => '\uD83D\uDE1E',
            ':S' => '\uD83D\uDE1F',
            '(sleepy)' => '\uD83D\uDE2A',
            '(cryingface)' => '\uD83D\uDE22',
            ';(' => '\uD83D\uDE2D',
            ':O' => '\uD83D\uDE32',
            '(handshake)' => '\uD83E\uDD1D',
            '(rockon)' => '\uD83E\uDD1F',
            '(zany)' => '\uD83D\uDE1C',
            '(shush)' => '\uD83E\uDD2B',
            '(chuckle)' => '\uD83E\uDD2D',
            '(puke)' => '\uD83E\uDD2E',
            '(brokenheart)' => '\uD83D\uDC94',
            ':D' => '\uD83D\uDE00',
            '(beaming)' => '\uD83D\uDE01',
            '(tearofjoys)' => '\uD83D\uDE02',
            '(smilingeyes)' => '\uD83D\uDE04',
            '(sweat)' => '\uD83D\uDE05',
            '(squint)' => '\uD83D\uDE06',
            '(angel)' => '\uD83D\uDE07',
            '(devil)' => '\uD83D\uDE08',
            ';)' => '\uD83D\uDE09',
            ':|' => '\uD83D\uDE10',
            '(expressionless)' => '\uD83D\uDE11',
            '(unamused)' => '\uD83D\uDE12',
            '(downcast)' => '\uD83D\uDE13',
            ':(' => '\uD83D\uDE41',
            ':-/' => '\uD83D\uDE15',
            '(confounded)' => '\uD83D\uDE16',
            '(kissingface)' => '\uD83D\uDE17',
            ':-*' => '\uD83D\uDE18',
            '(angry)' => '\uD83D\uDE20',
            '(pounting)' => '\uD83D\uDE21',
            '(persevering)' => '\uD83D\uDE23',
            '(steamnose)' => '\uD83D\uDE24',
            '(anguished)' => '\uD83D\uDE27',
            '(fearful)' => '\uD83D\uDE28',
            '(weary)' => '\uD83D\uDE29',
            '(anxious)' => '\uD83D\uDE30',
            '(scream)' => '\uD83D\uDE31',
            '(sleep)' => '\uD83D\uDE34',
            '(dizzy)' => '\uD83D\uDE35',
            '(emptymouth)' => '\uD83D\uDE36',
            '(medicalmask)' => '\uD83D\uDE37',
            '(frown)' => '\uD83D\uDE26',
            ':)' => '\uD83D\uDE42',
            '(upsidedown)' => '\uD83D\uDE43',
            '(rollingeyes)' => '\uD83D\uDE44',
            '(zipper)' => '\uD83E\uDD10',
            '(moneymouth)' => '\uD83E\uDD11',
            '(sick)' => '\uD83E\uDD12',
            '(nerd)' => '\uD83E\uDD13',
            '(think)' => '\uD83E\uDD14',
            '(injure)' => '\uD83E\uDD15',
            '(hug)' => '\uD83E\uDD17',
            '(nauseated)' => '\uD83E\uDD22',
            '(drooling)' => '\uD83E\uDD24',
            '(lie)' => '\uD83E\uDD25',
            '(sneeze)' => '\uD83E\uDD27',
            '(starstruck)' => '\uD83E\uDD29',
            '(star)' => '\uD83C\uDF1F',
            '(victory)' => '\u270C',
            '(heart)' => '\u2764',
        ];
    }

    public function process()
    {
        $iWorriedEmoticon = db()
            ->select('COUNT(emoticon_id)')
            ->from(':comment_emoticon')
            ->where([
                'title' => 'worried',
                ' AND image <> \'1f61f.png\''
            ])
            ->execute('getField');
        if ($iWorriedEmoticon) {
            db()->update(':comment_emoticon', [
                'image' => '1f61f.png'
            ], 'title = \'worried\'');
        }
        $iUnicode = db()
            ->select('COUNT(emoticon_id)')
            ->from(':comment_emoticon')
            ->where([
                'unicode IS NOT NULL'
            ])
            ->execute('getField');
        if (!$iUnicode) {
            foreach ($this->unicodeList as $sCode => $sUnicode) {
                db()->update(':comment_emoticon', [
                    'unicode' => $sUnicode
                ], 'code LIKE \''.$sCode.'\'');
            }
        }
    }
}