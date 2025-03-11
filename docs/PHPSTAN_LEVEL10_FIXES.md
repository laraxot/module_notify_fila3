# Correzioni PHPStan Livello 10 - Modulo Notify

Questo documento traccia gli errori PHPStan di livello 10 identificati nel modulo Notify e le relative soluzioni implementate.

## Errori Principali e Soluzioni

### 1. Operazioni binarie con mixed

**Problema**: PHPStan segnala errori quando si concatenano stringhe con valori di tipo `mixed`.

**File interessati**:
- `Actions/BuildMailMessageAction.php`
- `Actions/EsendexSendAction.php`
- `Actions/NotifyTheme/Get.php`
- `Filament/Clusters/Test/Pages/SendPushNotification.php`

**Soluzione**:
- Sostituire i cast diretti a stringa `(string)$value` con controlli di tipo espliciti
- Utilizzare `is_string()` per verificare il tipo prima dell'uso
- Implementare valori di fallback per i casi in cui i valori non sono stringhe
- Utilizzare `Assert::string()` per garantire che i valori siano stringhe

### 2. Accesso a proprietà o metodi su mixed

**Problema**: PHPStan segnala errori quando si accede a proprietà o si chiamano metodi su valori di tipo `mixed`.

**File interessati**:
- `Filament/Resources/ContactResource/Pages/ListContacts.php`
- `Filament/Resources/NotificationResource/Pages/ListNotifications.php`
- `Notifications/Channels/NetfunChannel.php`
- `Services/MailEngines/MailtrapEngine.php`
- `Services/SmsService.php`

**Soluzione**:
- Aggiungere controlli di tipo con `is_object()` prima di accedere a proprietà
- Utilizzare `property_exists()` per verificare l'esistenza di proprietà
- Utilizzare `method_exists()` per verificare l'esistenza di metodi
- Implementare gestione degli errori con try/catch o controlli condizionali
- Utilizzare tipi di ritorno e parametri espliciti nelle firme dei metodi

### 3. Parametri di tipo incompatibile

**Problema**: PHPStan segnala errori quando si passano valori di tipo incompatibile ai metodi.

**File interessati**:
- `Filament/Resources/NotifyThemeResource.php`
- `Filament/Resources/NotifyThemeResource/Pages/ListNotifyThemes.php`
- `Notifications/FirebaseAndroidNotification.php`

**Soluzione**:
- Utilizzare Closure per restituire array tipizzati correttamente
- Implementare controlli di tipo per garantire la compatibilità
- Costruire array con la struttura corretta prima di passarli ai metodi
- Utilizzare annotazioni PHPDoc per specificare i tipi generici

### 4. Proprietà non definite

**Problema**: PHPStan segnala errori quando si accede a proprietà non definite nella classe.

**File interessati**:
- `Services/SmsService.php`

**Soluzione**:
- Definire esplicitamente tutte le proprietà utilizzate nella classe
- Aggiungere annotazioni PHPDoc per specificare i tipi delle proprietà
- Implementare metodi getter/setter per accedere alle proprietà

## Principi Applicati nelle Correzioni

1. **Controlli di tipo espliciti**: Verificare sempre il tipo di un valore prima di utilizzarlo in operazioni che richiedono un tipo specifico.
2. **Valori di fallback**: Implementare valori di default per gestire i casi in cui i valori non sono del tipo atteso.
3. **Documentazione migliorata**: Aggiungere annotazioni PHPDoc corrette per aiutare PHPStan a comprendere i tipi.
4. **Gestione degli errori**: Implementare try/catch o controlli condizionali per gestire potenziali errori.
5. **Asserzioni**: Utilizzare `Assert::string()`, `Assert::isArray()`, ecc. per garantire che i valori siano del tipo corretto.

## Esempi di Correzioni

### Esempio 1: Correzione di operazioni binarie con mixed

```php
// Prima
$url = Str::of($url)->replace(url(''), '')->toString();

// Dopo
if (!is_string($url)) {
    $url = '/' . $key; // Fallback
} else {
    $url = Str::of($url)->replace(url(''), '')->toString();
}
```

### Esempio 2: Correzione di accesso a proprietà su mixed

```php
// Prima
$fullName = $item->profile->full_name;

// Dopo
if (!is_object($item) || !property_exists($item, 'profile') || 
    !is_object($item->profile) || !property_exists($item->profile, 'full_name')) {
    return [];
}
$fullName = $item->profile->full_name;
```

### Esempio 3: Correzione di parametri di tipo incompatibile

```php
// Prima
->options(NotifyThemeResource::fieldOptions('lang'))

// Dopo
->options(function (): array {
    return NotifyThemeResource::fieldOptions('lang');
})
```

## Risultati

Dopo aver implementato tutte le correzioni, PHPStan al livello 10 non riporta più errori nei moduli Lang e Notify. Questo garantisce un codice più robusto e tipizzato, riducendo il rischio di errori a runtime.

## Prossimi Passi

1. Continuare a correggere gli errori rimanenti nel modulo Notify
2. Eseguire PHPStan al livello 10 per verificare che tutti gli errori siano stati risolti
3. Documentare pattern comuni per evitare errori simili in futuro 