<?php

namespace Tests\Feature\Api;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\VendorModel;

class VendorDetailTest extends CIUnitTestCase
{
  use DatabaseTestTrait, FeatureTestTrait;

  protected $namespace = null;

  /**
   * Testet, ob der API-Aufruf für einen existierenden Vendor erfolgreich ist.
   */
  public function testShowReturnsSuccessWithValidUuid()
  {
    // 1. Vorbereiten (Arrange)
    // Wir holen uns einen echten Vendor aus der Datenbank, die für den Test verwendet wird.
    $vendor = model(VendorModel::class)->first();
    // Wir stellen sicher, dass wir einen Vendor gefunden haben, sonst kann der Test nicht laufen.
    $this->assertNotNull($vendor, 'Kein Vendor zum Testen in der Datenbank gefunden.');

    $url = "/api/vendors/details/" . $vendor->uuid;

    // 2. Ausführen (Act)
    // Wir simulieren einen GET-Aufruf an unsere API-Route.
    $result = $this->get($url);

    // 3. Überprüfen (Assert)
    // Wir erwarten eine erfolgreiche Antwort (Status 200 OK).
    $result->assertStatus(200);

    // Wir erwarten, dass die Antwort JSON ist.
    $result->assertJSON();

    // Wir überprüfen, ob im JSON der korrekte Vendor-Name enthalten ist.
    $result->assertJSONFragment(['vendor_name' => $vendor->name]);
  }

  /**
   * Testet, ob der API-Aufruf mit einer falschen UUID einen 404-Fehler wirft.
   */
  public function testShowReturnsNotFoundWithInvalidUuid()
  {
    // 1. Vorbereiten (Arrange)
    $url = "/api/vendors/details/eine-falsche-uuid";

    // 2. Ausführen (Act)
    $result = $this->get($url);

    // 3. Überprüfen (Assert)
    // Wir erwarten eine "Nicht gefunden"-Antwort (Status 404).
    $result->assertStatus(404);
  }
}
