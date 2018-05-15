DROP PROCEDURE IF EXISTS `SCP_SCPSP_Temp_DML`$$

CREATE PROCEDURE `SCP_SCPSP_Temp_DML`()
BEGIN
    /*
	创建者:
	创建日期: 2018-02-10
	描述： 应逻辑要求 添加 微信分享图片路径 存储字段  在 字段 name 后面添加字段 `sharepic` 
	修改记录: 
	--------------------------------------------------------
	修改者		修改日期		修改目的
                02/10       添加字段-`sharepic`
	--------------------------------------------------------
    */
	IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNs  WHERE table_schema='scp' AND table_name='os_examination' AND COLUMN_name='sharepic' )
	THEN
	ALTER TABLE `os_examination`   
	ADD COLUMN `sharepic` VARCHAR(240) DEFAULT NULL  COMMENT '微信分享图片路径信息' AFTER `name`;
	END IF;
END$$