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
- Mantener `writable/` fuera del acceso público; los documentos de solicitudes se guardan allí.

## Pruebas

`composer test` ejecuta PHPUnit sin solicitar cobertura, que requiere un controlador opcional como Xdebug. Para generar informes de cobertura, instalar y habilitar un controlador compatible y ejecutar PHPUnit con cobertura de forma explícita.
