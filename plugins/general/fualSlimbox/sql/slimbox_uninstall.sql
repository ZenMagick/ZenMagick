/**
 * Fual Slimbox for Zen v1.0
 *
 * @author Brian Tyler (btyler@math.ucl.ac.uk)
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 * @note All being good in the world this should remove Slimbox from the database
 */

SET @t4=0;
SELECT (@t4:=configuration_group_id) as t4 
FROM configuration_group
WHERE configuration_group_title= 'Fual Slimbox';
DELETE FROM configuration WHERE configuration_group_id = @t4;
DELETE FROM configuration_group WHERE configuration_group_id = @t4;