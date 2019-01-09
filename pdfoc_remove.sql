#NEXT_X_ROWS_AS_ONE_COMMAND:3
SELECT @pdfocid:=configuration_group_id
FROM configuration_group
WHERE configuration_group_title='PDF Order Center';

#NEXT_X_ROWS_AS_ONE_COMMAND:2
DELETE FROM configuration
WHERE configuration_group_id=@pdfocid;

#NEXT_X_ROWS_AS_ONE_COMMAND:3
DELETE FROM configuration_group
WHERE configuration_group_id=@pdfocid
LIMIT 1;

