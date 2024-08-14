Aplicación para enviar SMS de recordatorio a los pacientes usando la plataforma de Altiria

Hay que crear el config.php partiendo de config.php.new y completar los valores de los parametros.

Si se desea hacer pruebas, en el fichero enviar-sms.php hay una variable llamada PRODUCCION, a la que se le tiene que asignar el valor 'false'

Para que realice el envío a una hora determinada, hay que pogramar en las tareas programadas del servidor, que ejecute el archivo enviar-sms.bat

