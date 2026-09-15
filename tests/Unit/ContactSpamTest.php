<?php

namespace Tests\Unit;

use App\Support\ContactSpam;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * De spamscore van het contactformulier. De voorbeelden zijn (ingekort) echte
 * inzendingen uit contact_messages; de "echte aanvragen" zijn nagemaakt, want die
 * kwamen er sinds april 2026 niet één binnen via dit formulier.
 */
class ContactSpamTest extends TestCase
{
    #[DataProvider('spam')]
    public function test_spam_haalt_de_drempel(array $data): void
    {
        $o = ContactSpam::beoordeel($data);
        $this->assertGreaterThanOrEqual(ContactSpam::DREMPEL, $o['score'], implode('; ', $o['redenen']));
    }

    #[DataProvider('echt')]
    public function test_echte_aanvraag_blijft_onder_de_drempel(array $data): void
    {
        $o = ContactSpam::beoordeel($data);
        $this->assertLessThan(ContactSpam::DREMPEL, $o['score'], implode('; ', $o['redenen']));
    }

    public static function spam(): array
    {
        return [
            'wartaal zonder spaties' => [['name' => 'eougtkvdse', 'email' => 'x@immenseignite.info', 'message' => 'ydpjsdneyqytokiwjnvurodwsltntw']],
            'wartaal met spaties' => [['name' => 'EdwardMow', 'email' => 'x@gmx.us', 'message' => 'Egjnjmfnefjwdifj fkmdkdwdwkdwjj fkmfkengjkfmsdnfejfk mkfmkdmwjefnejfem']],
            'naam met cijferreeks' => [['name' => 'NAYUYUTY465753NERT', 'email' => 'x@analismail.com', 'message' => 'METRYTRE465753MAWRERGTRH']],
            'prijsvraag spaans' => [['name' => 'RobertDeeni', 'email' => 'x@gmail.com', 'message' => 'Hola, quería saber tu precio..']],
            'prijsvraag hongaars' => [['name' => 'RobertDeeni', 'email' => 'x@gmail.com', 'message' => 'Szia, meg akartam tudni az árát.']],
            'prijsvraag grieks' => [['name' => 'RobertDeeni', 'email' => 'x@gmail.com', 'message' => 'Γεια σου, ήθελα να μάθω την τιμή σας.']],
            'gokreclame' => [['name' => 'Caseymm', 'email' => 'x@mail.com', 'message' => 'The proof: 1 in 5 spins triggers a jackpot. Try now! Spin to Win Here']],
            'engelse verkoopmail' => [['name' => 'Joanna Riggs', 'email' => 'x@gmail.com', 'message' => "Hi, I just visited betergeregeld.com and wondered if you've ever considered a redesign."]],
            'eigen domein als afzender' => [['name' => 'Elisa', 'email' => 'info@betergeregeld.com', 'message' => 'Morning I wanted to reach out and let you know about our new dog harness.']],
            '419-mail' => [['name' => 'Terry Greg', 'email' => 'x@gmail.com', 'message' => 'Hello Dear, My name is Mr Terry Greg, I was the Head of Equity Investment with a bank.']],
            'herhaalde botnaam' => [['name' => 'RobertDeeni', 'email' => 'x@gmail.com', 'message' => 'Hi there, quick question about your services.', 'zelfde_naam_30d' => 11]],
        ];
    }

    public static function echt(): array
    {
        return [
            'nederlandse aanvraag met eigen site' => [['name' => 'Marieke de Groot', 'email' => 'marieke@bakkerijdegroot.nl', 'message' => "Hallo, wij hebben een verouderde website (https://bakkerijdegroot.nl) en willen graag weten wat een nieuwe site bij jullie kost. Kunnen jullie ons bellen?"]],
            'engelse aanvraag' => [['name' => 'Tom Fischer', 'email' => 'tom@fischer-consulting.de', 'message' => 'Hi, we are looking for a partner to build a customer portal for our clients in the Netherlands. Could we schedule a call next week?']],
            'korte aanvraag' => [['name' => 'Kees', 'email' => 'kees@garagejansen.nl', 'message' => 'Wat kost de AI telefoniste per maand? Groet, Kees']],
            'aanvraag met prijs-woord in het nederlands' => [['name' => 'Anja Vos', 'email' => 'anja@praktijkvos.nl', 'message' => 'Graag een prijs voor onderhoud van onze WordPress-site, ongeveer 20 pagina\'s.']],
        ];
    }
}
