# Comedor Universitario

Aplicación CodeIgniter 4 para la gestión de solicitudes de inscripción y renovación, documentación, beneficiarios, acreditaciones y reclamos.

## Requisitos y puesta en marcha

- PHP 8.2 o superior y las extensiones requeridas por CodeIgniter 4, incluyendo `intl`, `mbstring` y la extensión del motor de base de datos elegido.
- Composer.
- MySQL/MariaDB para la instalación habitual; PHPUnit usa la base SQLite en memoria configurada en `app/Config/Database.php`.

1. Instalar dependencias con `composer install`.
2. Copiar `env` a `.env` y configurar `CI_ENVIRONMENT`, `app.baseURL`, la conexión de base de datos y `encryption.key`.
3. Ejecutar las migraciones con `php spark migrate`.
4. Configurar el servidor web para apuntar a `public/`, no a la raíz del proyecto.
5. Ejecutar las pruebas con `composer test`.

No guardar `.env`, credenciales, documentos cargados ni archivos de sesión en el repositorio.

## Registro, verificación y recuperación

Crear una cuenta no activa automáticamente el beneficio del comedor: las cuentas nuevas se registran con `activo = 0`. La elegibilidad se gestiona por separado. Las cuentas existentes se consideran verificadas al ejecutar la migración de verificación para conservar su acceso; las cuentas nuevas deben verificar el correo antes de iniciar sesión.

La verificación vence a las 24 horas y los enlaces de recuperación de contraseña vencen a la hora. Se almacenan hashes de los tokens, no los tokens originales. Si un correo no llega, se puede solicitar un nuevo enlace desde la pantalla de inicio de sesión.

Configurar un servidor SMTP en el `.env` del entorno, sin guardar secretos en el repositorio:

```ini
email.protocol = smtp
email.fromEmail = "no-responder@example.invalid"
email.fromName = "Comedor Universitario"
email.SMTPHost = "smtp.example.invalid"
email.SMTPUser = "usuario-smtp"
email.SMTPPass = "secreto-smtp"
email.SMTPPort = 587
email.SMTPCrypto = tls
email.SMTPTimeout = 10
```

Reemplazar los valores de ejemplo por los datos del proveedor institucional. Mantener el `.env` con permisos restrictivos y verificar el envío en el ambiente de despliegue. Si SMTP no está configurado o falla, el sistema registra el error y deja disponible la opción de reenviar el enlace; no se debe considerar el flujo de correo operativo hasta probar su entrega.
Si el secreto SMTP contiene caracteres especiales, guardarlo entre comillas en el `.env`.

## Gestión del beneficio del comedor

El Responsable puede activar o dar de baja el beneficio de estudiantes sin modificar su cuenta ni su acceso al sistema. Cada cambio requiere un motivo y se guarda en `historial_beneficios` con el estado anterior y nuevo, el estudiante, el Responsable y la fecha. La pantalla de beneficiarios muestra todos los estudiantes, permite acreditar saldo solo a quienes tienen el beneficio activo y presenta los últimos cambios registrados.

Al incorporar esta funcionalidad en una instalación existente, ejecutar `php spark migrate` para crear la tabla de auditoría. Hacer un respaldo de la base antes de aplicar migraciones en producción.

## Calendario académico

Las fechas oficiales no se generan automáticamente. La aplicación solo acepta solicitudes dentro de los períodos configurados; fuera de ellos, el formulario queda bloqueado. Los períodos de 2027 deben cargarse cuando se publique el calendario institucional.

Se puede configurar la variable `academic.terms` en `.env` como un arreglo JSON de períodos, por ejemplo:

```ini
academic.terms = '[{"nombre":"1° cuatrimestre 2027","inicio":"FECHA-INICIO-AAAA-MM-DD","fin":"FECHA-FIN-AAAA-MM-DD"},{"nombre":"2° cuatrimestre 2027","inicio":"FECHA-INICIO-AAAA-MM-DD","fin":"FECHA-FIN-AAAA-MM-DD"}]'
```

Reemplazar cada fecha de ejemplo por la fecha oficial en formato `AAAA-MM-DD`. Los períodos deben estar ordenados, no superponerse y tener nombre, inicio y fin válidos. La configuración se valida al utilizar el calendario; fechas ausentes o inválidas deben corregirse antes de habilitar solicitudes para el nuevo período.

Los días de aviso y cierre se configuran en `app/Config/Cuatrimestres.php`.

## Actualización de solicitudes vencidas

Las solicitudes en estado `recibida` pasan a `en revision` al superar las 24 horas. La actualización se ejecuta mediante el comando:

```console
php spark solicitudes:actualizar-vencidas
```

Programar ese comando para ejecutarse al menos una vez por hora. En Windows, usar el Programador de tareas con `php.exe` como programa, `spark solicitudes:actualizar-vencidas` como argumentos y la raíz del proyecto como directorio de inicio. En Linux, se puede configurar una tarea cron equivalente. El usuario del proceso debe tener acceso a la configuración y a la base de datos de la aplicación.

## Despliegue

- Establecer `CI_ENVIRONMENT = production` en el entorno de producción.
- Servir el sitio por HTTPS y configurar correctamente el host y `app.baseURL`.
- Mantener `DBDebug` desactivado en producción y proteger las credenciales de base de datos y la clave de cifrado.
- Mantener `writable/` fuera del acceso público; los documentos de solicitudes y los archivos de sesión se guardan allí.
- Usar cookies de sesión `Secure` en producción, `HttpOnly` y `SameSite=Lax`; no desactivar la regeneración/destrucción del identificador de sesión.
- Realizar copias de seguridad cifradas de la base de datos y de los documentos privados, almacenarlas separadas del servidor y probar periódicamente su restauración.
- Definir con la institución un plazo de conservación y borrado para documentos, copias de seguridad y registros; no conservarlos indefinidamente por defecto.
- Revisar periódicamente permisos del servidor, dependencias, registros de errores y configuración HTTPS. No registrar contraseñas, tokens ni enlaces de verificación/recuperación; configurar el servidor web para excluir o redactar las rutas de esos enlaces en los registros de acceso.

## Pruebas

`composer test` ejecuta PHPUnit sin solicitar cobertura, que requiere un controlador opcional como Xdebug. Para generar informes de cobertura, instalar y habilitar un controlador compatible y ejecutar PHPUnit con cobertura de forma explícita.
