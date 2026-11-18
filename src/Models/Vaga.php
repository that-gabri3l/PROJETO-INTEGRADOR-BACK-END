<?php 

class Vaga
{
    private int $id;
    private string $titulo;
    private string $descricao;
    private string $requesito;
    private float $salarioMin;
    private float $salarioMax;
    private float $salarioExibido;
    private string $localizacao;
    private Regime $regime;
    private string $beneficio;
    private DateTimeImmutable $dataPublicacao;
    private StatusVaga $status;

    public function __construct(
        int $id,
        string $titulo,
        string $descricao,
        string $requesito,
        float $salarioMin,
        float $salarioMax,
        float $salarioExibido,
        string $localizacao,
        Regime $regime,
        string $beneficio,
        DateTimeImmutable $dataPublicacao,
        StatusVaga $status
    ) {
        $this->id = $id;
        $this->titulo = $titulo;
        $this->descricao = $descricao;
        $this->requesito = $requesito;
        $this->salarioMin = $salarioMin;
        $this->salarioMax = $salarioMax;
        $this->salarioExibido = $salarioExibido;
        $this->localizacao = $localizacao;
        $this->regime = $regime;
        $this->beneficio = $beneficio;
        $this->dataPublicacao = $dataPublicacao;
        $this->status = $status;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): void
    {
        $this->titulo = $titulo;
    }

    public function getDescricao(): string
    {
        return $this->descricao;
    }

    public function setDescricao(string $descricao): void
    {
        $this->descricao = $descricao;
    }

    public function getRequesito(): string
    {
        return $this->requesito;
    }

    public function setRequesito(string $requesito): void
    {
        $this->requesito = $requesito;
    }

    public function getSalarioMin(): float
    {
        return $this->salarioMin;
    }

    public function setSalarioMin(float $salarioMin): void
    {
        $this->salarioMin = $salarioMin;
    }

    public function getSalarioMax(): float
    {
        return $this->salarioMax;
    }

    public function setSalarioMax(float $salarioMax): void
    {
        $this->salarioMax = $salarioMax;
    }

    public function getSalarioExibido(): float
    {
        return $this->salarioExibido;
    }

    public function setSalarioExibido(float $salarioExibido): void
    {
        $this->salarioExibido = $salarioExibido;
    }

    public function getLocalizacao(): string
    {
        return $this->localizacao;
    }

    public function setLocalizacao(string $localizacao): void
    {
        $this->localizacao = $localizacao;
    }

    public function getRegime(): Regime
    {
        return $this->regime;
    }

    public function setRegime(Regime $regime): void
    {
        $this->regime = $regime;
    }

    public function getBeneficio(): string
    {
        return $this->beneficio;
    }

    public function setBeneficio(string $beneficio): void
    {
        $this->beneficio = $beneficio;
    }

    public function getDataPublicacao(): DateTimeImmutable
    {
        return $this->dataPublicacao;
    }

    public function setDataPublicacao(DateTimeImmutable $dataPublicacao): void
    {
        $this->dataPublicacao = $dataPublicacao;
    }

    public function getStatus(): StatusVaga
    {
        return $this->status;
    }

    public function setStatus(StatusVaga $status): void
    {
        $this->status = $status;
    }
}