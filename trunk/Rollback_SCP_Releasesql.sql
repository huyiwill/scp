USE `scp`;

DELIMITER $$

DROP PROCEDURE IF EXISTS SCP_SCPSP_Temp_DML$$
CREATE PROCEDURE SCP_SCPSP_Temp_DML()
BEGIN
	IF EXISTS (SELECT 1 FROM information_schema.columns  WHERE table_schema='scp' AND table_name='os_examination' AND COLUMN_name='sharepic' )
	THEN		
		ALTER TABLE `os_examination` DROP COLUMN `sharepic`;
	END IF;
END$$

CALL SCP_SCPSP_Temp_DML()$$
DROP PROCEDURE IF EXISTS SCP_SCPSP_Temp_DML$$
