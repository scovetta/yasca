<?
declare(encoding='UTF-8');
namespace Yasca;

trait SingleFileContentsPlugin {
	abstract public function getResultIterator($fileContents, $filename);
}