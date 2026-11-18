<?php

class Candidato 
{
    private int $id;
    private string $nome;
    private int $telefone;
    private string $senha;
    private string $email;
    private string $cidade;
    private DateTimeImmutable $dataNascimento;
    private string $fotoPerfil;
    private string $cargoAtual;
    private string $areaAtuacao;
    private string $experiencia;
    private string $escolaridade;
    private Regime $regimePreferido;
    private string $sobre;
    private string $linkedin;
    private DateTimeImmutable $dataCriacao;

    public function __construct(
    int $id,
    string $nome,
    int $telefone,
    string $senha,
    string $email,
    string $cidade,
    DateTimeImmutable $dataNascimento,
    string $fotoPerfil,
    string $cargoAtual,
    string $areaAtuacao,
    string $experiencia,
    string $escolaridade,
    Regime $regimePreferido,
    string $sobre,
    string $linkedin,
    DateTimeImmutable $dataCriacao,
    )
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->telefone = $telefone;
        $this->senha = $senha;
        $this->email = $email;
        $this->cidade = $cidade;
        $this->dataNascimento = $dataNascimento;
        $this->fotoPerfil = $fotoPerfil;
        $this->cargoAtual = $cargoAtual;
        $this->areaAtuacao = $areaAtuacao;
        $this->experiencia = $experiencia;
        $this->escolaridade = $escolaridade;
        $this->regimePreferido = $regimePreferido;
        $this->sobre = $sobre;
        $this->linkedin = $linkedin;
        $this->dataCriacao = $dataCriacao;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): void
    {
        $this->nome = $nome;
    }

    public function getTelefone(): int
    {
        return $this->telefone;
    }

    public function setTelefone(int $telefone): void
    {
        $this->telefone = $telefone;
    }

    public function getSenha(): string
    {
        return $this->senha;
    }

    public function setSenha(string $senha): void
    {
        $this->senha = $senha;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getCidade(): string
    {
        return $this->cidade;
    }

    public function setCidade(string $cidade): void
    {
        $this->cidade = $cidade;
    }

    public function getDataNascimento(): DateTimeImmutable
    {
        return $this->dataNascimento;
    }

    public function setDataNascimento(DateTimeImmutable $dataNascimento): void
    {
        $this->dataNascimento = $dataNascimento;
    }

    public function getFotoPerfil(): string
    {
        return $this->fotoPerfil;
    }

    public function setFotoPerfil(string $fotoPerfil): void
    {
        $this->fotoPerfil = $fotoPerfil;
    }

    public function getCargoAtual(): string
    {
        return $this->cargoAtual;
    }

    public function setCargoAtual(string $cargoAtual): void
    {
        $this->cargoAtual = $cargoAtual;
    }

    public function getAreaAtuacao(): string
    {
        return $this->areaAtuacao;
    }

    public function setAreaAtuacao(string $areaAtuacao): void
    {
        $this->areaAtuacao = $areaAtuacao;
    }

    public function getExperiencia(): string
    {
        return $this->experiencia;
    }

    public function setExperiencia(string $experiencia): void
    {
        $this->experiencia = $experiencia;
    }

    public function getEscolaridade(): string
    {
        return $this->escolaridade;
    }

    public function setEscolaridade(string $escolaridade): void
    {
        $this->escolaridade = $escolaridade;
    }

    public function getRegimePreferido(): Regime
    {
        return $this->regimePreferido;
    }

    public function setRegimePreferido(Regime $regimePreferido): void
    {
        $this->regimePreferido = $regimePreferido;
    }

    public function getSobre(): string
    {
        return $this->sobre;
    }

    public function setSobre(string $sobre): void
    {
        $this->sobre = $sobre;
    }

    public function getLinkedin(): string
    {
        return $this->linkedin;
    }

    public function setLinkedin(string $linkedin): void
    {
        $this->linkedin = $linkedin;
    }

    public function getDataCriacao(): DateTimeImmutable
    {
        return $this->dataCriacao;
    }

    public function setDataCriacao(DateTimeImmutable $dataCriacao): void
    {
        $this->dataCriacao = $dataCriacao;
    }
}