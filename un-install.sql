SELECT @pdfocid:=configuration_group_id
FROM configuration_group
WHERE configuration_group_title='PDF Order Center';

DELETE FROM configuration
WHERE configuration_group_id=@pdfocid;

DELETE FROM configuration_group
WHERE configuration_group_id=@pdfocid
LIMIT 1;
