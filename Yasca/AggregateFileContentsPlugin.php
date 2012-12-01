<?
declare(encoding='UTF-8');
namespace Yasca;

trait AggregateFileContentsPlugin {
	abstract public function apply($fileContents, $filename);
	abstract public function getResultIterator();
}